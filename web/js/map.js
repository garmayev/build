const Helper = {
    createElement: (tagName, attributes = {}, events = {}) => {
        const el = document.createElement(tagName);
        if (attributes) {
            for (const key in attributes) {
                el.setAttribute(key, attributes[key]);
            }
        }
        if (events) {
            for (const key in events) {
                el.addEventListener(key, events[key]);
            }
        }
        return el;
    }
}
let map, placemark;
let latitudeInput, longitudeInput;

window.initMap = (container, position, formName = undefined) =>
{
    ymaps.ready(init)

    function init() {
        map = new ymaps.Map(container, {
            center: position,
            zoom: 10,
            controls: []
        })

        if (formName) {
            latitudeInput = Helper.createElement('input', {
                name: formName + '[latitude]',
                type: 'hidden',
            })
            longitudeInput = Helper.createElement('input', {
                name: formName + '[longitude]',
                type: 'hidden',
            })
            document.querySelector("#"+container).parentNode.append(latitudeInput, longitudeInput);
        }

        map.events.add('click', function(event) {
            const coords = event.get('coords')
            if (placemark) {
                placemark.geometry.setCoordinates(coords);
            } else {
                placemark = new ymaps.Placemark(coords, {
                    balloonContent: '',
                }, {
                    preset: 'islands#dotIcon',
                    iconColor: '#735184'
                })
                map.geoObjects.add(placemark);
            }
            if (formName) {
                latitudeInput.value = coords[0];
                longitudeInput.value = coords[1];
            }
        })
    }
}

window.destroyMap = () =>
{
    map.destroy();
}


class Map {
    _container;
    _instance;
    _latitude;
    _latitudeContainer;
    _longitude;
    _longitudeContainer;
    _suggestView;
    _place;

    constructor(container, position, formName, suggestView) {
        this._latitude = position.latitude;
        this._latitudeContainer = Helper.createElement('input', {
            type: 'hidden',
            name: `${formName}[latitude]`,
            value: this._latitude
        })
        this._longitude = position.longitude;
        this._longitudeContainer = Helper.createElement('input', {
            type: 'hidden',
            name: `${formName}[longitude]`,
            value: this._longitude
        })
        this._container = document.getElementById(container)

        if (suggestView) {
            this.initSuggest(suggestView, position.address)
        }
        this.initMap()

        this._container.append(this._latitudeContainer, this._longitudeContainer)
    }
    initSuggest(suggestView, address = null) {
        this._suggestView = new ymaps.SuggestView(suggestView, {
            boundedBy: [[50, 107], [56, 117]]
        });
        this.address = address
        this._suggestView.events.add('select', this.onSelect.bind(this))
    }
    initMap() {
        this._place = new ymaps.Placemark(this.coordinates, {
            iconCaption: 'поиск...'
        }, {
            preset: 'islands#violetDotIconWithCaption',
            draggable: true
        })
        this._instance = new ymaps.Map(this._container, {
            center: [this.latitude, this.longitude],
            zoom: 15,
            controls: [],
            duration: 1000,
        })
        this._instance.events.add('click', this.onClick.bind(this));
        this._instance.geoObjects.add(this._place)
    }
    get coordinates() {
        return [this._latitude, this._longitude]
    }
    set coordinates(coordinates) {
        this.latitude = coordinates[0]
        this.longitude = coordinates[1]
    }
    get latitude() {
        return this._latitude;
    }
    set latitude(value) {
        this._latitude = value
        this._latitudeContainer.value = value
        this.move(this.coordinates)
    }
    get longitude() {
        return this._longitude;
    }
    set longitude(value) {
        this._longitude = value;
        this._longitudeContainer.value = value
        this.move(this.coordinates)
    }

    get address() {
        return ymaps.suggest([this._latitude, this._longitude]).then(function (response) {
            console.log(response)
        })
    }
    set address(value) {
        return ymaps.geocode(value).then(function (response) {
            const firstGeoObject = response.geoObjects.get(0);
            this.coordinates = Array.isArray(value) ? value : firstGeoObject.geometry.getCoordinates()
            this._place.properties.set({
                iconCaption: firstGeoObject.getAddressLine(),
                balloonContent: firstGeoObject.getAddressLine(),
            })
            this.move();
        }.bind(this))
    }
    move(coordinates = null) {
        if (coordinates) {
            this._place.geometry.setCoordinates(coordinates)
            this._instance.setCenter(coordinates)
        } else {
            console.log(this.coordinates)
            this._place.geometry.setCoordinates(this.coordinates)
            this._instance.setCenter(this.coordinates)
        }
    }
    onSelect(e) {
        this.address = e.originalEvent.item.value
        this._place.properties.set({
            iconCaption: e.originalEvent.item.value,
            balloonContent: e.originalEvent.item.value,
        })
    }
    onClick(event) {
        this.address = event.get('coords')
    }
}
