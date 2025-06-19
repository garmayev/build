export const Helper = {
    url: "//build.local",
    types: [
        'Больше',
        'Меньше',
        'Равно',
        'Не равно'
    ],
    status: [
        'Выключен',
        'Активен',
        'Отключен'
    ],
    findById: (array: Array<any>, value:number) => {
        return array.find(item => {
            return item.id === value
        })
    },
    buildForm: (data: Array<any>) => {
        const form = new FormData();

        for (const key in data) {
            form.append(key, data[key]);
        }
        return form
    },
    zeroPad: (num:number, places:number) => String(num).padStart(places, '0'),
}