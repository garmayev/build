<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактор цепочек ответов бота</title>
    <style>
        :root {
            --primary-color: #4a76a8;
            --secondary-color: #f5f5f5;
            --border-color: #ddd;
            --hover-color: #e9e9e9;
            --active-color: #d9d9d9;
            --text-color: #333;
            --error-color: #ff6b6b;
        }

        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
            color: var(--text-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
            color: var(--primary-color);
        }

        .editor-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .nodes-panel {
            width: 250px;
            background: var(--secondary-color);
            padding: 15px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        .node-type {
            padding: 10px;
            margin-bottom: 10px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            cursor: grab;
            transition: all 0.2s;
            user-select: none;
            -webkit-user-drag: element;
        }

        .node-type:hover {
            background: var(--hover-color);
        }

        .flow-area {
            flex-grow: 1;
            min-height: 600px;
            border: 2px dashed var(--border-color);
            border-radius: 6px;
            position: relative;
            overflow: hidden;
            background-image: linear-gradient(to right, #f5f5f5 1px, transparent 1px),
            linear-gradient(to bottom, #f5f5f5 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .node {
            position: absolute;
            width: 200px;
            background: white;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            cursor: move;
            z-index: 1;
        }

        .node-header {
            background: var(--primary-color);
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }

        .node-content {
            padding: 12px;
        }

        .node-connector {
            width: 12px;
            height: 12px;
            background: var(--primary-color);
            border-radius: 50%;
            position: absolute;
            cursor: pointer;
            z-index: 2;
        }

        .node-connector.input {
            top: -6px;
            left: calc(50% - 6px);
        }

        .node-connector.output {
            bottom: -6px;
            left: calc(50% - 6px);
        }

        .connection {
            position: absolute;
            pointer-events: none;
            z-index: 0;
        }

        .connection path {
            stroke: var(--primary-color);
            stroke-width: 2;
            fill: none;
        }

        .properties-panel {
            width: 300px;
            background: var(--secondary-color);
            padding: 15px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input, textarea, select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #3a6595;
        }

        .btn-delete {
            background: var(--error-color);
        }

        .btn-delete:hover {
            background: #e05555;
        }

        .response-option {
            background: white;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            position: relative;
        }

        .remove-option {
            position: absolute;
            right: 5px;
            top: 5px;
            background: none;
            border: none;
            color: var(--error-color);
            cursor: pointer;
            font-weight: bold;
        }

        .node.start-node {
            border-left: 4px solid #4CAF50;
        }

        .node.message-node {
            border-left: 4px solid #2196F3;
        }

        .node.question-node {
            border-left: 4px solid #FFC107;
        }

        .node.end-node {
            border-left: 4px solid #F44336;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .zoom-controls {
            display: flex;
            gap: 5px;
            margin-left: auto;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Редактор цепочек ответов бота</h1>

    <div class="toolbar">
        <button id="save-btn">Сохранить</button>
        <button id="load-btn">Загрузить</button>
        <button id="clear-btn">Очистить</button>
        <button id="load-example">Пример</button>
        <div class="zoom-controls">
            <button id="zoom-in">+</button>
            <button id="zoom-out">-</button>
            <button id="zoom-reset">100%</button>
        </div>
    </div>

    <div class="editor-container">
        <div class="nodes-panel">
            <h3>Элементы</h3>
            <div class="node-type" draggable="true" data-type="start">Начало</div>
            <div class="node-type" draggable="true" data-type="message">Сообщение</div>
            <div class="node-type" draggable="true" data-type="question">Вопрос</div>
            <div class="node-type" draggable="true" data-type="end">Конец</div>
        </div>

        <div class="flow-area" id="flow-area">
            <!-- Здесь будут появляться узлы -->
        </div>

        <div class="properties-panel" id="properties-panel">
            <h3>Свойства</h3>
            <div id="properties-content">
                <p>Выберите элемент для редактирования</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Состояние приложения
        const state = {
            nodes: [],
            connections: [],
            selectedNode: null,
            nextId: 1,
            scale: 1,
            tempConnection: null,
            draggingNode: null,
            dragOffset: { x: 0, y: 0 }
        };

        // Элементы DOM
        const flowArea = document.getElementById('flow-area');
        const propertiesPanel = document.getElementById('properties-panel');
        const propertiesContent = document.getElementById('properties-content');
        const nodeTypes = document.querySelectorAll('.node-type');
        const saveBtn = document.getElementById('save-btn');
        const loadBtn = document.getElementById('load-btn');
        const clearBtn = document.getElementById('clear-btn');
        const loadExampleBtn = document.getElementById('load-example');
        const zoomInBtn = document.getElementById('zoom-in');
        const zoomOutBtn = document.getElementById('zoom-out');
        const zoomResetBtn = document.getElementById('zoom-reset');

        // Инициализация
        init();

        function init() {
            // Добавляем обработчики для элементов панели
            nodeTypes.forEach(nodeType => {
                nodeType.addEventListener('dragstart', handleDragStart);
            });

            // Обработчики для области потока
            flowArea.addEventListener('dragover', handleDragOver);
            flowArea.addEventListener('drop', handleDrop);
            flowArea.addEventListener('click', handleFlowAreaClick);

            // Кнопки управления
            saveBtn.addEventListener('click', saveFlow);
            loadBtn.addEventListener('click', loadFlow);
            clearBtn.addEventListener('click', clearFlow);
            loadExampleBtn.addEventListener('click', loadExample);
            zoomInBtn.addEventListener('click', () => updateZoom(0.1));
            zoomOutBtn.addEventListener('click', () => updateZoom(-0.1));
            zoomResetBtn.addEventListener('click', () => updateZoom(0, true));

            // Обработчики для перемещения элементов
            document.addEventListener('mousemove', handleDocumentMouseMove);
            document.addEventListener('mouseup', handleDocumentMouseUp);
        }

        function handleDragStart(e) {
            e.dataTransfer.setData('type', e.target.dataset.type);
            e.dataTransfer.effectAllowed = 'copy';
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        }

        function handleDrop(e) {
            e.preventDefault();
            const type = e.dataTransfer.getData('type');
            const rect = flowArea.getBoundingClientRect();
            const x = (e.clientX - rect.left - rect.width * (1 - state.scale) / 2) / state.scale;
            const y = (e.clientY - rect.top - rect.height * (1 - state.scale) / 2) / state.scale;

            createNode(type, x, y);
        }

        function handleFlowAreaClick(e) {
            if (e.target === flowArea) {
                state.selectedNode = null;
                updatePropertiesPanel();
            }
        }

        function createNode(type, x, y) {
            const id = state.nextId++;
            const node = { id, type, x, y, data: {} };

            // Устанавливаем данные по умолчанию в зависимости от типа
            switch(type) {
                case 'start':
                    node.data = { text: 'Начало диалога' };
                    break;
                case 'message':
                    node.data = { text: 'Текст сообщения' };
                    break;
                case 'question':
                    node.data = {
                        text: 'Ваш вопрос?',
                        options: [
                            { text: 'Вариант 1', nextNodeId: null },
                            { text: 'Вариант 2', nextNodeId: null }
                        ]
                    };
                    break;
                case 'end':
                    node.data = { text: 'Конец диалога' };
                    break;
            }

            state.nodes.push(node);
            renderNode(node);
            selectNode(node);
        }

        function renderNode(node) {
            // Удаляем старую ноду, если она уже существует
            const oldNodeEl = document.getElementById(`node-${node.id}`);
            if (oldNodeEl) oldNodeEl.remove();

            const nodeEl = document.createElement('div');
            nodeEl.id = `node-${node.id}`;
            nodeEl.className = `node ${node.type}-node`;
            nodeEl.style.left = `${node.x}px`;
            nodeEl.style.top = `${node.y}px`;
            nodeEl.style.transform = `scale(${state.scale})`;
            nodeEl.style.transformOrigin = 'top left';

            // Заголовок ноды
            const headerEl = document.createElement('div');
            headerEl.className = 'node-header';

            const titleMap = {
                'start': 'Начало',
                'message': 'Сообщение',
                'question': 'Вопрос',
                'end': 'Конец'
            };

            headerEl.innerHTML = `
                    <span>${titleMap[node.type]}</span>
                    <button class="btn-delete" data-node-id="${node.id}">×</button>
                `;

            // Содержимое ноды
            const contentEl = document.createElement('div');
            contentEl.className = 'node-content';

            // Усеченный текст для отображения в ноде
            let previewText = '';
            if (node.type === 'question') {
                previewText = node.data.text;
                if (previewText.length > 30) previewText = previewText.substring(0, 30) + '...';
            } else {
                previewText = node.data.text;
                if (previewText.length > 50) previewText = previewText.substring(0, 50) + '...';
            }

            contentEl.textContent = previewText;

            // Разъемы для соединений
            const inputConnector = document.createElement('div');
            inputConnector.className = 'node-connector input';
            inputConnector.dataset.nodeId = node.id;
            inputConnector.dataset.connectorType = 'input';

            const outputConnector = document.createElement('div');
            outputConnector.className = 'node-connector output';
            outputConnector.dataset.nodeId = node.id;
            outputConnector.dataset.connectorType = 'output';

            // Только для вопросов добавляем выходной разъем
            if (node.type !== 'question') {
                outputConnector.style.display = 'none';
            }

            // Собираем ноду
            nodeEl.appendChild(headerEl);
            nodeEl.appendChild(contentEl);
            nodeEl.appendChild(inputConnector);
            nodeEl.appendChild(outputConnector);

            // Добавляем в DOM
            flowArea.appendChild(nodeEl);

            // Обработчики событий
            nodeEl.addEventListener('click', (e) => {
                if (e.target.classList.contains('btn-delete')) {
                    deleteNode(node.id);
                    return;
                }
                selectNode(node);
                e.stopPropagation();
            });

            // Перетаскивание ноды
            headerEl.addEventListener('mousedown', (e) => {
                if (e.target.classList.contains('btn-delete')) return;

                state.draggingNode = node;
                const rect = nodeEl.getBoundingClientRect();
                state.dragOffset = {
                    x: (e.clientX - rect.left) / state.scale,
                    y: (e.clientY - rect.top) / state.scale
                };

                e.preventDefault();
            });

            // Обработчики для разъемов
            inputConnector.addEventListener('mousedown', handleConnectorMouseDown);
            outputConnector.addEventListener('mousedown', handleConnectorMouseDown);

            // Обновляем соединения
            updateConnections();
        }

        function handleConnectorMouseDown(e) {
            e.stopPropagation();

            // Удаляем предыдущую временную линию
            const oldLine = document.getElementById('temp-connection');
            if (oldLine) oldLine.remove();

            const nodeId = parseInt(e.target.dataset.nodeId);
            const connectorType = e.target.dataset.connectorType;

            state.tempConnection = {
                nodeId,
                connectorType,
                x: e.clientX,
                y: e.clientY
            };

            document.addEventListener('mousemove', handleTempConnectionMove);
            document.addEventListener('mouseup', handleTempConnectionUp);
        }

        function handleTempConnectionMove(e) {
            if (!state.tempConnection) return;

            // Очищаем старую временную линию
            const oldLine = document.getElementById('temp-connection');
            if (oldLine) oldLine.remove();

            const startNodeEl = document.getElementById(`node-${state.tempConnection.nodeId}`);
            if (!startNodeEl) return;

            const connectorEl = startNodeEl.querySelector(`.node-connector.${state.tempConnection.connectorType}`);
            if (!connectorEl) return;

            const connectorRect = connectorEl.getBoundingClientRect();
            const startX = connectorRect.left + connectorRect.width / 2;
            const startY = connectorRect.top + connectorRect.height / 2;

            const svgEl = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgEl.id = 'temp-connection';
            svgEl.style.position = 'absolute';
            svgEl.style.top = '0';
            svgEl.style.left = '0';
            svgEl.style.width = '100%';
            svgEl.style.height = '100%';
            svgEl.style.pointerEvents = 'none';
            svgEl.style.zIndex = '0';

            const pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            pathEl.setAttribute('stroke', '#4a76a8');
            pathEl.setAttribute('stroke-width', '2');
            pathEl.setAttribute('fill', 'none');

            // Рисуем кривую Безье от разъема к курсору
            const endX = e.clientX;
            const endY = e.clientY;
            const controlX1 = startX + (endX - startX) / 2;
            const controlY1 = startY;
            const controlX2 = startX + (endX - startX) / 2;
            const controlY2 = endY;

            pathEl.setAttribute('d', `M${startX},${startY} C${controlX1},${controlY1} ${controlX2},${controlY2} ${endX},${endY}`);

            svgEl.appendChild(pathEl);
            flowArea.appendChild(svgEl);
        }

        function handleTempConnectionUp(e) {
            // Всегда удаляем временную линию
            const tempLine = document.getElementById('temp-connection');
            if (tempLine) tempLine.remove();

            if (!state.tempConnection) return;

            // Проверяем, был ли отпущен курсор над другим разъемом
            const target = document.elementFromPoint(e.clientX, e.clientY);
            if (target && target.classList.contains('node-connector')) {
                const targetNodeId = parseInt(target.dataset.nodeId);
                const targetConnectorType = target.dataset.connectorType;

                // Проверяем, что соединяем выход с входом
                if (state.tempConnection.connectorType !== targetConnectorType) {
                    const sourceNodeId = state.tempConnection.connectorType === 'output'
                        ? state.tempConnection.nodeId
                        : targetNodeId;
                    const targetNodeId = state.tempConnection.connectorType === 'input'
                        ? state.tempConnection.nodeId
                        : targetNodeId;

                    // Проверяем, что соединение еще не существует
                    const exists = state.connections.some(conn =>
                        conn.sourceNodeId === sourceNodeId && conn.targetNodeId === targetNodeId);

                    if (!exists) {
                        state.connections.push({ sourceNodeId, targetNodeId });
                        updateConnections();
                    }
                }
            }

            state.tempConnection = null;
            document.removeEventListener('mousemove', handleTempConnectionMove);
            document.removeEventListener('mouseup', handleTempConnectionUp);
        }

        function updateConnections() {
            // Удаляем все существующие соединения
            document.querySelectorAll('.connection').forEach(el => el.remove());

            // Рендерим все соединения
            state.connections.forEach(connection => {
                const sourceNodeEl = document.getElementById(`node-${connection.sourceNodeId}`);
                const targetNodeEl = document.getElementById(`node-${connection.targetNodeId}`);

                if (sourceNodeEl && targetNodeEl) {
                    const outputConnector = sourceNodeEl.querySelector('.node-connector.output');
                    const inputConnector = targetNodeEl.querySelector('.node-connector.input');

                    if (outputConnector && inputConnector) {
                        const outputRect = outputConnector.getBoundingClientRect();
                        const inputRect = inputConnector.getBoundingClientRect();
                        const flowAreaRect = flowArea.getBoundingClientRect();

                        const startX = (outputRect.left - flowAreaRect.left + outputRect.width / 2) / state.scale;
                        const startY = (outputRect.top - flowAreaRect.top + outputRect.height / 2) / state.scale;
                        const endX = (inputRect.left - flowAreaRect.left + inputRect.width / 2) / state.scale;
                        const endY = (inputRect.top - flowAreaRect.top + inputRect.height / 2) / state.scale;

                        const svgEl = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                        svgEl.className = 'connection';
                        svgEl.style.position = 'absolute';
                        svgEl.style.top = '0';
                        svgEl.style.left = '0';
                        svgEl.style.width = '100%';
                        svgEl.style.height = '100%';
                        svgEl.style.pointerEvents = 'none';

                        const pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        pathEl.setAttribute('stroke', '#4a76a8');
                        pathEl.setAttribute('stroke-width', '2');
                        pathEl.setAttribute('fill', 'none');

                        // Рисуем кривую Безье между узлами
                        const controlX1 = startX + (endX - startX) / 2;
                        const controlY1 = startY;
                        const controlX2 = startX + (endX - startX) / 2;
                        const controlY2 = endY;

                        pathEl.setAttribute('d', `M${startX},${startY} C${controlX1},${controlY1} ${controlX2},${controlY2} ${endX},${endY}`);

                        svgEl.appendChild(pathEl);
                        flowArea.appendChild(svgEl);
                    }
                }
            });
        }

        function selectNode(node) {
            state.selectedNode = node;
            updatePropertiesPanel();

            // Подсвечиваем выбранную ноду
            document.querySelectorAll('.node').forEach(el => {
                el.style.boxShadow = 'none';
            });

            const nodeEl = document.getElementById(`node-${node.id}`);
            if (nodeEl) {
                nodeEl.style.boxShadow = '0 0 0 2px #4a76a8';
            }
        }

        function updatePropertiesPanel() {
            if (!state.selectedNode) {
                propertiesContent.innerHTML = '<p>Выберите элемент для редактирования</p>';
                return;
            }

            const node = state.selectedNode;

            switch(node.type) {
                case 'start':
                    propertiesContent.innerHTML = `
                            <div class="form-group">
                                <label>Текст приветствия</label>
                                <textarea id="node-text">${escapeHtml(node.data.text)}</textarea>
                            </div>
                            <button id="save-properties">Сохранить</button>
                        `;
                    break;

                case 'message':
                    propertiesContent.innerHTML = `
                            <div class="form-group">
                                <label>Текст сообщения</label>
                                <textarea id="node-text">${escapeHtml(node.data.text)}</textarea>
                            </div>
                            <button id="save-properties">Сохранить</button>
                        `;
                    break;

                case 'question':
                    let optionsHtml = '';
                    node.data.options.forEach((option, index) => {
                        optionsHtml += `
                                <div class="response-option">
                                    <button class="remove-option" data-option-index="${index}">×</button>
                                    <div class="form-group">
                                        <label>Текст варианта</label>
                                        <input type="text" class="option-text" value="${escapeHtml(option.text)}" data-option-index="${index}">
                                    </div>
                                    <div class="form-group">
                                        <label>Следующий узел</label>
                                        <select class="option-next-node" data-option-index="${index}">
                                            <option value="">-- Не указан --</option>
                                            ${getAvailableNodesOptions(node.id, option.nextNodeId)}
                                        </select>
                                    </div>
                                </div>
                            `;
                    });

                    propertiesContent.innerHTML = `
                            <div class="form-group">
                                <label>Текст вопроса</label>
                                <textarea id="node-text">${escapeHtml(node.data.text)}</textarea>
                            </div>
                            <h4>Варианты ответов</h4>
                            <div id="question-options">
                                ${optionsHtml}
                            </div>
                            <button id="add-option">Добавить вариант</button>
                            <button id="save-properties">Сохранить</button>
                        `;
                    break;

                case 'end':
                    propertiesContent.innerHTML = `
                            <div class="form-group">
                                <label>Завершающий текст</label>
                                <textarea id="node-text">${escapeHtml(node.data.text)}</textarea>
                            </div>
                            <button id="save-properties">Сохранить</button>
                        `;
                    break;
            }

            // Добавляем обработчики событий
            document.getElementById('save-properties')?.addEventListener('click', saveNodeProperties);
            document.getElementById('add-option')?.addEventListener('click', addQuestionOption);

            // Обработчики для удаления вариантов ответа
            document.querySelectorAll('.remove-option').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.dataset.optionIndex);
                    removeQuestionOption(index);
                });
            });

            // Обработчики для изменения следующих узлов
            document.querySelectorAll('.option-next-node').forEach(select => {
                select.addEventListener('change', function() {
                    const index = parseInt(this.dataset.optionIndex);
                    const nextNodeId = this.value ? parseInt(this.value) : null;

                    // Обновляем в состоянии
                    state.selectedNode.data.options[index].nextNodeId = nextNodeId;

                    // Обновляем соединения
                    updateConnections();
                });
            });
        }

        function saveNodeProperties() {
            if (!state.selectedNode) return;

            const node = state.selectedNode;
            const text = document.getElementById('node-text')?.value;

            if (text !== undefined) {
                node.data.text = text;
            }

            // Для вопросов сохраняем тексты вариантов
            if (node.type === 'question') {
                document.querySelectorAll('.option-text').forEach(input => {
                    const index = parseInt(input.dataset.optionIndex);
                    node.data.options[index].text = input.value;
                });
            }

            // Перерисовываем ноду с обновленными данными
            renderNode(node);
        }

        function addQuestionOption() {
            if (!state.selectedNode || state.selectedNode.type !== 'question') return;

            state.selectedNode.data.options.push({
                text: 'Новый вариант',
                nextNodeId: null
            });

            updatePropertiesPanel();
        }

        function removeQuestionOption(index) {
            if (!state.selectedNode || state.selectedNode.type !== 'question') return;

            state.selectedNode.data.options.splice(index, 1);
            updatePropertiesPanel();
        }

        function getAvailableNodesOptions(currentNodeId, selectedNodeId) {
            return state.nodes
                .filter(node => node.id !== currentNodeId && node.type !== 'start')
                .map(node => {
                    const selected = node.id === selectedNodeId ? 'selected' : '';
                    return `<option value="${node.id}" ${selected}>${getNodeTitle(node)}</option>`;
                })
                .join('');
        }

        function getNodeTitle(node) {
            const typeMap = {
                'start': 'Начало',
                'message': 'Сообщение',
                'question': 'Вопрос',
                'end': 'Конец'
            };

            let text = node.data.text;
            if (text.length > 20) text = text.substring(0, 20) + '...';

            return `${typeMap[node.type]} (${text})`;
        }

        function deleteNode(nodeId) {
            // Удаляем ноду
            state.nodes = state.nodes.filter(node => node.id !== nodeId);

            // Удаляем связанные соединения
            state.connections = state.connections.filter(conn =>
                conn.sourceNodeId !== nodeId && conn.targetNodeId !== nodeId);

            // Удаляем элемент из DOM
            const nodeEl = document.getElementById(`node-${nodeId}`);
            if (nodeEl) nodeEl.remove();

            // Обновляем соединения
            updateConnections();

            // Сбрасываем выбранную ноду
            if (state.selectedNode?.id === nodeId) {
                state.selectedNode = null;
                updatePropertiesPanel();
            }
        }

        function saveFlow() {
            const data = {
                nodes: state.nodes,
                connections: state.connections,
                nextId: state.nextId
            };

            const json = JSON.stringify(data, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = 'bot-flow.json';
            a.click();

            URL.revokeObjectURL(url);
        }

        function loadFlow() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.json';

            input.onchange = e => {
                const file = e.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = event => {
                    try {
                        const data = JSON.parse(event.target.result);

                        // Очищаем текущее состояние
                        clearFlow();

                        // Загружаем новое состояние
                        state.nodes = data.nodes || [];
                        state.connections = data.connections || [];
                        state.nextId = data.nextId || 1;

                        // Перерисовываем все ноды
                        state.nodes.forEach(node => renderNode(node));
                        updateConnections();
                    } catch (error) {
                        alert('Ошибка при загрузке файла: ' + error.message);
                    }
                };
                reader.readAsText(file);
            };

            input.click();
        }

        function clearFlow() {
            // Очищаем состояние
            state.nodes = [];
            state.connections = [];
            state.selectedNode = null;
            state.nextId = 1;

            // Очищаем DOM
            document.querySelectorAll('.node, .connection').forEach(el => el.remove());

            // Очищаем панель свойств
            updatePropertiesPanel();
        }

        function loadExample() {
            // Пример простого диалога
            const example = {
                nodes: [
                    {
                        id: 1,
                        type: 'start',
                        x: 100,
                        y: 100,
                        data: { text: 'Привет! Как я могу помочь?' }
                    },
                    {
                        id: 2,
                        type: 'question',
                        x: 100,
                        y: 200,
                        data: {
                            text: 'Выберите вариант:',
                            options: [
                                { text: 'Информация', nextNodeId: 3 },
                                { text: 'Поддержка', nextNodeId: 4 }
                            ]
                        }
                    },
                    {
                        id: 3,
                        type: 'message',
                        x: 300,
                        y: 150,
                        data: { text: 'Наш сайт: example.com' }
                    },
                    {
                        id: 4,
                        type: 'message',
                        x: 300,
                        y: 250,
                        data: { text: 'Пишите на support@example.com' }
                    },
                    {
                        id: 5,
                        type: 'end',
                        x: 100,
                        y: 400,
                        data: { text: 'До свидания!' }
                    }
                ],
                connections: [
                    { sourceNodeId: 1, targetNodeId: 2 },
                    { sourceNodeId: 2, targetNodeId: 3 },
                    { sourceNodeId: 2, targetNodeId: 4 },
                    { sourceNodeId: 3, targetNodeId: 5 },
                    { sourceNodeId: 4, targetNodeId: 5 }
                ],
                nextId: 6
            };

            // Очищаем текущее состояние
            clearFlow();

            // Загружаем пример
            state.nodes = example.nodes;
            state.connections = example.connections;
            state.nextId = example.nextId;

            // Перерисовываем все ноды
            state.nodes.forEach(node => renderNode(node));
            updateConnections();
        }

        function updateZoom(delta, reset = false) {
            if (reset) {
                state.scale = 1;
            } else {
                state.scale = Math.max(0.5, Math.min(2, state.scale + delta));
            }

            // Применяем масштаб ко всем нодам
            document.querySelectorAll('.node').forEach(nodeEl => {
                nodeEl.style.transform = `scale(${state.scale})`;
            });

            // Обновляем соединения
            updateConnections();
        }

        function handleDocumentMouseMove(e) {
            // Обработка перетаскивания ноды
            if (state.draggingNode) {
                const node = state.draggingNode;
                const nodeEl = document.getElementById(`node-${node.id}`);
                if (!nodeEl) return;

                const rect = flowArea.getBoundingClientRect();
                const x = (e.clientX - rect.left - state.dragOffset.x) / state.scale;
                const y = (e.clientY - rect.top - state.dragOffset.y) / state.scale;

                node.x = x;
                node.y = y;
                nodeEl.style.left = `${x}px`;
                nodeEl.style.top = `${y}px`;

                // Обновляем соединения
                updateConnections();
            }

            // Обработка временного соединения
            if (state.tempConnection) {
                handleTempConnectionMove(e);
            }
        }

        function handleDocumentMouseUp() {
            // Удаляем временную линию, если она есть
            const tempLine = document.getElementById('temp-connection');
            if (tempLine) tempLine.remove();

            state.draggingNode = null;
            state.tempConnection = null;
        }

        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return unsafe.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    });
</script>
</body>
</html>