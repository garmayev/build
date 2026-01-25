<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class VCardParser
 * Парсер контактной информации в формате vCard
 * 
 * @package app\components
 */
class VCardParser extends Component
{
    /**
     * @var string Версия vCard
     */
    public $version;
    
    /**
     * @var string Идентификатор продукта
     */
    public $prodid;
    
    /**
     * @var array Распарсенные данные
     */
    private $_data = [];
    
    /**
     * @var array Ошибки парсинга
     */
    private $_errors = [];
    
    /**
     * Основной метод парсинга vCard
     * 
     * @param string $vcardContent Содержимое vCard
     * @return bool Успешность парсинга
     */
    public function parse(string $vcardContent): bool
    {
        $this->_data = [];
        $this->_errors = [];
        
        if (empty($vcardContent)) {
            $this->_errors[] = 'Пустое содержимое vCard';
            return false;
        }
        
        $lines = explode("\n", $vcardContent);
        $inVCard = false;
        
        foreach ($lines as $index => $line) {
            $line = trim($line);
            $lineNumber = $index + 1;
            
            if (empty($line)) {
                continue;
            }
            
            switch ($line) {
                case 'BEGIN:VCARD':
                    $inVCard = true;
                    break;
                    
                case 'END:VCARD':
                    $inVCard = false;
                    break;
                    
                default:
                    if ($inVCard) {
                        $this->parseLine($line, $lineNumber);
                    }
                    break;
            }
        }
        
        if (!$inVCard && empty($this->_data)) {
            $this->_errors[] = 'Не найден корректный блок vCard';
            return false;
        }
        
        return empty($this->_errors);
    }
    
    /**
     * Парсинг отдельной строки vCard
     * 
     * @param string $line Строка для парсинга
     * @param int $lineNumber Номер строки
     */
    private function parseLine(string $line, int $lineNumber): void
    {
        // Продолжение многострочных полей
        if (isset($line[0]) && $line[0] === ' ') {
            $this->handleContinuedLine($line);
            return;
        }
        
        if (strpos($line, ':') === false) {
            $this->_errors[] = "Строка $lineNumber: некорректный формат (отсутствует ':')";
            return;
        }
        
        list($fieldPart, $value) = explode(':', $line, 2);
        
        // Обработка специальных полей
        switch (strtoupper($fieldPart)) {
            case 'VERSION':
                $this->version = $value;
                return;
                
            case 'PRODID':
                $this->prodid = $value;
                return;
        }
        
        // Парсинг поля с параметрами
        $this->parseFieldWithParams($fieldPart, $value);
    }
    
    /**
     * Обработка продолжения многострочных полей
     * 
     * @param string $line Продолжение строки
     */
    private function handleContinuedLine(string $line): void
    {
        if (empty($this->_data)) {
            return;
        }
        
        // Добавляем к последнему значению
        end($this->_data);
        $lastKey = key($this->_data);
        
        if (is_array($this->_data[$lastKey])) {
            $this->_data[$lastKey]['value'] .= ltrim($line);
        } else {
            $this->_data[$lastKey] .= ltrim($line);
        }
    }
    
    /**
     * Парсинг поля с параметрами
     * 
     * @param string $fieldPart Часть строки до ':'
     * @param string $value Значение поля
     */
    private function parseFieldWithParams(string $fieldPart, string $value): void
    {
        $fieldName = $fieldPart;
        $params = [];
        
        if (strpos($fieldPart, ';') !== false) {
            $parts = explode(';', $fieldPart);
            $fieldName = array_shift($parts);
            
            foreach ($parts as $param) {
                if (strpos($param, '=') !== false) {
                    list($paramName, $paramValue) = explode('=', $param, 2);
                    $params[$paramName] = $this->parseParamValue($paramValue);
                }
            }
        }
        
        // Декодирование кодированных строк
        if (strpos($value, ';ENCODING=QUOTED-PRINTABLE:') !== false) {
            $value = $this->decodeQuotedPrintable($value);
        }
        
        // Декодирование base64
        if (strpos($value, ';ENCODING=BASE64:') !== false) {
            $value = $this->decodeBase64($value);
        }
        
        // Обработка структурированных значений (ADR, N и т.д.)
        if (in_array(strtoupper($fieldName), ['ADR', 'N', 'ORG'])) {
            $value = $this->parseStructuredValue($value);
        }
        
        // Сохранение данных
        $this->storeField($fieldName, $value, $params);
    }
    
    /**
     * Парсинг значения параметра
     * 
     * @param string $paramValue Значение параметра
     * @return array|string
     */
    private function parseParamValue(string $paramValue)
    {
        if (strpos($paramValue, ',') !== false) {
            return explode(',', $paramValue);
        }
        
        return $paramValue;
    }
    
    /**
     * Декодирование quoted-printable
     * 
     * @param string $value Закодированное значение
     * @return string Декодированное значение
     */
    private function decodeQuotedPrintable(string $value): string
    {
        // Упрощенная декодировка
        $value = quoted_printable_decode($value);
        return str_replace('=0D=0A', "\n", $value);
    }
    
    /**
     * Декодирование base64
     * 
     * @param string $value Закодированное значение
     * @return string Декодированное значение
     */
    private function decodeBase64(string $value): string
    {
        $parts = explode(':', $value, 2);
        return count($parts) === 2 ? base64_decode($parts[1]) : $value;
    }
    
    /**
     * Парсинг структурированных значений
     * 
     * @param string $value Структурированное значение
     * @return array
     */
    private function parseStructuredValue(string $value): array
    {
        return explode(';', $value);
    }
    
    /**
     * Сохранение поля в данных
     * 
     * @param string $fieldName Имя поля
     * @param mixed $value Значение
     * @param array $params Параметры
     */
    private function storeField(string $fieldName, $value, array $params = []): void
    {
        $fieldNameUpper = strtoupper($fieldName);
        
        if (empty($params)) {
            $this->_data[$fieldNameUpper] = $value;
            return;
        }
        
        $fieldData = [
            'value' => $value,
            'params' => $params
        ];
        
        // Для полей, которые могут встречаться несколько раз
        if (in_array($fieldNameUpper, ['TEL', 'EMAIL', 'ADR', 'URL'])) {
            if (!isset($this->_data[$fieldNameUpper])) {
                $this->_data[$fieldNameUpper] = [];
            }
            $this->_data[$fieldNameUpper][] = $fieldData;
        } else {
            $this->_data[$fieldNameUpper] = $fieldData;
        }
    }
    
    /**
     * Получение распарсенных данных
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->_data;
    }
    
    /**
     * Получение ошибок
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->_errors;
    }
    
    /**
     * Проверка наличия ошибок
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->_errors);
    }
    
    /**
     * Получение полного имени (FN)
     * 
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->_data['FN'] ?? null;
    }
    
    /**
     * Получение телефонов
     * 
     * @return array
     */
    public function getPhones(): array
    {
        $phones = [];
        
        if (isset($this->_data['TEL'])) {
            if (isset($this->_data['TEL']['value'])) {
                // Один телефон
                $phones[] = [
                    'number' => $this->_data['TEL']['value'],
                    'type' => $this->_data['TEL']['params']['TYPE'] ?? 'unknown'
                ];
            } else {
                // Несколько телефонов
                foreach ($this->_data['TEL'] as $tel) {
                    $phones[] = [
                        'number' => $tel['value'],
                        'type' => $tel['params']['TYPE'] ?? 'unknown'
                    ];
                }
            }
        }
        
        return $phones;
    }
    
    /**
     * Получение email-адресов
     * 
     * @return array
     */
    public function getEmails(): array
    {
        $emails = [];
        
        if (isset($this->_data['EMAIL'])) {
            if (isset($this->_data['EMAIL']['value'])) {
                // Один email
                $emails[] = [
                    'email' => $this->_data['EMAIL']['value'],
                    'type' => $this->_data['EMAIL']['params']['TYPE'] ?? 'unknown'
                ];
            } elseif (is_array($this->_data['EMAIL'])) {
                // Несколько email
                foreach ($this->_data['EMAIL'] as $email) {
                    if (isset($email['value'])) {
                        $emails[] = [
                            'email' => $email['value'],
                            'type' => $email['params']['TYPE'] ?? 'unknown'
                        ];
                    }
                }
            }
        }
        
        return $emails;
    }
    
    /**
     * Получение структурированного имени (N)
     * 
     * @return array|null
     */
    public function getStructuredName(): ?array
    {
        if (!isset($this->_data['N'])) {
            return null;
        }
        
        $parts = is_array($this->_data['N']) ? $this->_data['N'] : explode(';', $this->_data['N']);
        
        return [
            'lastName' => $parts[0] ?? null,
            'firstName' => $parts[1] ?? null,
            'middleName' => $parts[2] ?? null,
            'prefix' => $parts[3] ?? null,
            'suffix' => $parts[4] ?? null,
        ];
    }
    
    /**
     * Получение организации
     * 
     * @return string|null
     */
    public function getOrganization(): ?string
    {
        if (!isset($this->_data['ORG'])) {
            return null;
        }
        
        if (is_array($this->_data['ORG'])) {
            return implode(', ', $this->_data['ORG']);
        }
        
        return $this->_data['ORG'];
    }
    
    /**
     * Получение должности
     * 
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->_data['TITLE'] ?? null;
    }
    
    /**
     * Экспорт данных в массив для сохранения в БД
     * 
     * @return array
     */
    public function toArray(): array
    {
        $nameParts = $this->getStructuredName();
        
        return [
            'full_name' => $this->getFullName(),
            'first_name' => $nameParts['firstName'] ?? null,
            'last_name' => $nameParts['lastName'] ?? null,
            'middle_name' => $nameParts['middleName'] ?? null,
            'phones' => $this->getPhones(),
            'emails' => $this->getEmails(),
            'organization' => $this->getOrganization(),
            'title' => $this->getTitle(),
            'raw_data' => $this->_data,
        ];
    }
    
    /**
     * Статический метод для быстрого парсинга
     * 
     * @param string $vcardContent Содержимое vCard
     * @return array|null
     */
    public static function parseQuick(string $vcardContent): ?array
    {
        $parser = new self();
        
        if ($parser->parse($vcardContent)) {
            return $parser->toArray();
        }
        
        Yii::error('Ошибка парсинга vCard: ' . implode(', ', $parser->getErrors()));
        return null;
    }
}