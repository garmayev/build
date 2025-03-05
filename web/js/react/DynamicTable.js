const Helper =
    {
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
        },
        resolve: (path, obj) => {
            if (path) {
                return path.split('.').reduce(function (prev, curr) {
                    return prev ? prev[curr] : null
                }, obj || self)
            }
        },
        findById: (array, id) => {
            let selected = null;
            for (let i = 0; i < array.length; i++) {
                if (array[i].id === id) {
                    selected = array[i];
                }
            }
            return selected;
        }
    }

function Requirement({propertyUrl, categoryId, setRequirements, requirements, dataKey}) {
    const types = [
        yii.t['modal.type.less'],
        yii.t['modal.type.more'],
        yii.t['modal.type.equal'],
        yii.t['modal.type.not-equal']
    ];
    const [propertyList, setPropertyList] = React.useState([]);
    const [dimensionList, setDimensionList] = React.useState([]);
    const [selectedProperty, setSelectedProperty] = React.useState();
    const [selectedDimension, setSelectedDimension] = React.useState();
    const [selectedType, setSelectedType] = React.useState();
    const [value, setValue] = React.useState(0);

    React.useEffect(() => {
        fetch(`${propertyUrl}?id=${categoryId}`)
            .then(response => response.json())
            .then(result => {
                const res = result.results;
                if (res) {
                    setPropertyList(res);
                    setSelectedProperty(res[0])
                    setSelectedType(types[0]);
                    setDimensionList(res[0].dimensions)
                    setSelectedDimension(res[0].dimensions[0])
                }
            })
    }, [categoryId])

    React.useEffect(() => {
        const my = {property: selectedProperty, type: selectedType, value: value, dimension: selectedDimension};
        // {property: selectedProperty, type: selectedType, value: value, dimension: selectedDimension};
        setRequirements(requirements.map((item, index) => {
            // console.log(dataKey, index)
            return index === dataKey ? my : item
        }))
    }, [selectedProperty, selectedType, selectedDimension, value]);

    return (
        <div className={'row mb-3 requirement'} data-key={dataKey}>
            <div className={'col-3'}>
                <select className={'form-control'} onChange={(e) => {
                    setSelectedProperty(Helper.findById(propertyList, Number.parseInt(e.target.value)));
                    // setRequirements(dataKey, {property: Helper.findById(propertyList, Number.parseInt(e.target.value))})
                }}>
                    {propertyList && propertyList.map((property, index) => (
                        <option key={index} value={property.id}>{property.title}</option>))}
                </select>
            </div>
            <div className={'col-3'}>
                <select className={'form-control'} onChange={(e) => {
                    setSelectedType(types[e.target.value])
                    // setRequirements(dataKey, {type: types[e.target.value]})
                }}>
                    {types && types.map((type, index) => (
                        <option key={index} value={index}>{type}</option>))}
                </select>
            </div>
            <div className={'col-3'}>
                <input type={'number'} className={'form-control'} onChange={(e) => {
                    setValue(Number.parseInt(e.target.value));
                    // setRequirements(dataKey, {value: Number.parseInt(e.target.value)})
                }}/>
            </div>
            <div className={'col-3'}>
                <select className={'form-control'} onChange={(e) => {
                    setSelectedDimension(Helper.findById(dimensionList, Number.parseInt(e.target.value)));
                    // setRequirements(dataKey, {dimension: Helper.findById(dimensionList, Number.parseInt(e.target.value))})
                }}>
                    {dimensionList && dimensionList.map((dimension, index) => (
                        <option key={index} value={dimension.id}>{dimension.title}</option>))}
                </select>
            </div>
        </div>
    )
}

/**
 *
 * @param data
 * @param header = [
 *     {
 *         header: 'Title',
 *     }
 * ]
 * @param formName
 * @returns {JSX.Element}
 * @constructor
 */
function Table({data, header, formName}) {
    console.log(data);
    const [list, setList] = React.useState(data);

    return (
        <table className={'table table-striped'}>
            <thead>
            <tr>
                {header.map((head, index) => (<th key={index}>{head.header}</th>))}
                <th></th>
            </tr>
            </thead>
            <tbody>
            {data?.map((item, index) => {
                if (item) {
                    return (
                        <tr key={`row-${index}`} data-key={index}>
                            {
                                header.map((head, headIndex) => {
                                    let result, value;
                                    if (typeof head.key === 'object') {
                                        let t = []
                                        let r = Helper.resolve(`requirements`, item);
                                        if (r) {
                                            console.log(r);
                                            for (let i = 0; i < r.length; i++) {
                                                let res = [];
                                                head.key.subkey.map(a => {
                                                    let text = Helper.resolve(`${i}.${a}`, r);
                                                    res.push(text);
                                                    // res.push(`<input type='hidden' name=`${formName}[${index}]${head.value}` value={}}/>`)
                                                });
                                                head.key.values.map(a => {
                                                    let value = Helper.resolve(`${i}.${a.inputValue}`, r);
                                                    let text = `${formName}[${index}][requirements][${i}]${a.inputName}`;
                                                    t.push(<input type={'hidden'}
                                                                  name={text}
                                                                  value={value}/>)
                                                })
                                                t.push(res.join(' '))
                                            }
                                            result = t.map((a, i) => <p className={'mb-0'} key={i}>{a}</p>);
                                        }
                                    } else {
                                        value = Helper.resolve(head.inputValue, item);
                                        let text = Helper.resolve(head.key, item)
                                        result = <p>{text}
                                            <input type={'hidden'} name={`${formName}[${index}]${head.inputName}`}
                                                   value={value}/>
                                        </p>;
                                    }
                                    return (
                                        <td key={`column-${index}-${headIndex}`}>{result}</td>
                                    )
                                })
                            }
                            <td data-target={index}>
                                <a className={'fas fa-pen'} href={'#'}></a>
                                <a className={'fas fa-trash'} href={'#'}></a>
                            </td>
                        </tr>
                    );
                }
            })}
            </tbody>
        </table>
    )
}

function Modal({categoryUrl, propertyUrl, onClick}) {
    const [categoryList, setCategoryList] = React.useState([]);
    const [requirements, setRequirements] = React.useState([]);
    const [category, _setCategory] = React.useState({});
    const [count, setCount] = React.useState(1);

    const setCategory = (value) => {
        _setCategory(value);
        setRequirements([]);
    }

    const id = 'add-filter';
    React.useEffect(() => {
        // fetch(categoryUrl)
        fetch(categoryUrl)
            .then(response => response.json())
            .then(result => {
                console.log(result)
                setCategoryList(result.results);
                setCategory(result.results[0]);
            })
    }, [])
    React.useEffect(() => {
        setRequirements([]);
    }, [category])

    return (
        <>
            <span className={'btn btn-primary'} data-toggle={'modal'}
                  onClick={() => {
                      _setCategory(categoryList[0]);
                      setCount(1);
                      setRequirements([]);
                  }}
                  data-target={`#${id}`}>{yii.t['submit.title']}</span>
            <div className={'modal fade'} id={id} tabIndex={-1} aria-labelledby={`${id}-label`} aria-hidden={true}>
                <div className={'modal-dialog modal-dialog-scrollable modal-lg nodal-xl'}>
                    <div className={'modal-content'}>
                        <div className={'modal-header'}>
                            <h5 className={'modal-title'} id={`${id}-label`}>{yii.t['modal.header']}</h5>
                            <button type={'button'} className={'close'} data-dismiss={'modal'}
                                    aria-label={yii.t['modal.close']}>
                                <span aria-hidden={true}>&times;</span>
                            </button>
                        </div>
                        <div className={'modal-body'}>
                            <div className={'form-group'}>
                                <select className={'form-control'} onChange={(e) => {
                                    const t = Helper.findById(categoryList, Number.parseInt(e.target.value));
                                    setCategory(t)
                                }}>
                                    {categoryList && categoryList.map((item, index) => (
                                        <option key={index} value={item.id}>{item.title}</option>))}
                                </select>
                            </div>
                            <div className={'form-group'}>
                                <input type={'number'} className={'form-control'} value={count} onChange={(event) => {
                                    setCount(event.target.value);
                                }}/>
                            </div>
                            <div className={'form-group'}>
                                <button type={'button'} className={'btn btn-primary'} onClick={() => {
                                    setRequirements([...requirements, {}]);
                                }}>{yii.t['modal.addRequirement']}</button>
                            </div>
                            {requirements && requirements.map((item, index) => (
                                <Requirement
                                    key={index}
                                    dataKey={index}
                                    categoryId={category.id}
                                    propertyUrl={propertyUrl}
                                    requirements={requirements}
                                    setRequirements={setRequirements}/>
                            ))}
                        </div>
                        <div className={'modal-footer'}>
                            <button type={'button'} className={'btn btn-success'} data-dismiss={'modal'}
                                    onClick={() => {
                                        onClick?.call(this, {
                                            category: category,
                                            count: count,
                                            requirements: requirements
                                        })
                                    }}>{yii.t['modal.save']}</button>
                            <button type={'button'} className={'btn btn-danger'}
                                    data-dismiss={'modal'}>{yii.t['modal.close']}</button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}

/**
 *
 * @param data
 * @param tableHeader
 * @param dataUrl
 * @param propertyUrl
 * @param categoryUrl
 * @param formName
 * @returns {Element}
 * @constructor
 */
function DynamicTable({tableHeader, dataUrl, propertyUrl, categoryUrl, formName, children}) {
    const [dataList, _setDataList] = React.useState([]);
    const setDataList = (value) => {
        _setDataList([...dataList, value]);
    }

    React.useEffect(() => {
        fetch(dataUrl)
            .then(response => response.json())
            .then(result => {
                _setDataList(result);
            })
    }, [])

    return (
        <>
            <Modal onClick={setDataList} categoryUrl={categoryUrl} propertyUrl={propertyUrl}/>
            <Table data={dataList} header={tableHeader} formName={formName}/>
        </>
    )
}
