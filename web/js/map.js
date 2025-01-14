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