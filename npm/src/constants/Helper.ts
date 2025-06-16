export const Helper = {
    url: "https://build.amgcompany.ru",
    types: [
        'Больше',
        'Меньше',
        'Равно',
        'Не равно'
    ],
    findById: (array: Array<any>, value:number) => {
        return array.find(item => {
            return item.id === value
        })
    }
}