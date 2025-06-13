class Order {
    _id: number | undefined;

    get id() {
        return this._id;
    }

    set id(value) {
        this._id = value;
    }

    constructor() {
    }
}

export default Order