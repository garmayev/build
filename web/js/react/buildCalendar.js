const daysInMonth = (month, year) => {
    const monthStart = new Date(year, month, 1);
    const monthEnd = new Date(year, month + 1, 1);
    return (monthEnd - monthStart) / (1000 * 60 * 60 * 24);
}
const findByDate = (array, date) => {
    for (const element of array) {
        if (element.date === date) return element
    }
    return false
}
const months = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"]

const Modal = ({visible, callback, hide, position}) => {
    return (
        <>
            <div className={`modal-bg ${visible ? "show" : "hide"}`} onClick={hide}>
            </div>
            <div className={"modal"} style={{
                top: position.y,
                left: position.x,
                display: visible ? "block" : "none",
                width: `${modalWidth}px`
            }}>
                <div className={"modal-header"}>
                    <h5 className={"modal-header-title"}>Title</h5>
                    <span className={"modal-header-close"} onClick={hide}>&times;</span>
                </div>
                <div className={"modal-body"}>
                    <div className={"form-group"}>
                        <input type={"number"} className={"form-input"} id={"count"}/>
                    </div>
                </div>
                <div className={"modal-footer"}>
                    <button onClick={hide} className={"btn btn-danger"}>Cancel</button>
                    <button onClick={() => {
                        callback()
                    }} className={"btn btn-success"}>OK
                    </button>
                </div>
            </div>
        </>
    )
}
const PrevIcon = ({size = "24px", color = "#5f6368"}) => {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" height={size} viewBox="0 -960 960 960" width={size} fill={color}>
            <path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/>
        </svg>
    )
}
const NextIcon = ({size = "24px", color = "#5f6368"}) => {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" height={size} viewBox="0 -960 960 960" width={size} fill={color}>
            <path d="m321-80-71-71 329-329-329-329 71-71 400 400L321-80Z"/>
        </svg>
    )
}

const modalWidth = 400;
export default function BuildCalendar({dataset = [], url = ""}) {
    const [data, setData] = React.useState(dataset)
    const [currentDate, setCurrentDate] = React.useState(new Date())
    const [count, setCount] = React.useState(0)
    const [coworkerId, setCoworkerId] = React.useState()
    const labels = [];
    const [modalVisible, setModalVisible] = React.useState(false)
    const [modalPosition, setModalPosition] = React.useState({x: -2000, y: -2000})
    const [summary, setSummary] = useState(0)
    const prevMonth = () => {
        const month = currentDate.getMonth();
        if (month > 0) {
            currentDate.setMonth(currentDate.getMonth() - 1)
        } else {
            currentDate.setMonth(11)
            currentDate.setFullYear(currentDate.getFullYear() - 1)
        }
        setCurrentDate(new Date(currentDate))
    }
    const nextMonth = () => {
        const month = currentDate.getMonth();
        if (month < 11) {
            currentDate.setMonth(currentDate.getMonth() + 1)
        } else {
            currentDate.setMonth(0)
            currentDate.setFullYear(currentDate.getFullYear() + 1)
        }
        setCurrentDate(new Date(currentDate))
    }
    const setHours = (coworker_id, order_id, date, count) => {
        console.log(coworkerId)
        console.log("Set hours")
    }
    const showModal = (event) => {
        if (event.clientX + modalWidth > document.body.clientWidth) {
            setModalPosition({y: event.clientY, x: event.clientX - modalWidth})
        } else {
            setModalPosition({y: event.clientY, x: event.clientX})
        }
        setCoworkerId(event.target.closest("tr").getAttribute("data-key"))
        setModalVisible(true);
        setSummary(0)
    }
    React.useEffect(() => {
        setCount(daysInMonth(currentDate.getMonth(), currentDate.getFullYear()))
        const params = new URLSearchParams({
            year: currentDate.getFullYear(),
            month: currentDate.getMonth() < 10 ? `0${currentDate.getMonth() + 1}` : currentDate.getMonth() + 1
        });
        if (url) {
            fetch(url + "?" + params.toString())
                .then(response => response.json())
                .then(response => {
                    setData(response)
                })
                .catch(error => console.error(error.message))
        }
    }, [currentDate]);
    React.useEffect(() => {
        if (data.length) {
            let result = 0;
            data.forEach((item) => {
                item.data.forEach((el) => {
                    result += el.count
                })
            })
            setSummary(result)
        }
    }, [data]);

    for (let i = 1; i < count + 1; i++) {
        labels.push(`${i < 10 ? "0" + i : i}`)
    }

    return (
        <>
            <Modal visible={modalVisible} position={modalPosition} callback={() => {
                setModalVisible(false)
                setHours()
            }} hide={() => {
                setModalVisible(false)
            }}/>
            <table width={"100%"}>
                <thead>
                <tr>
                    <th key={`prev-month`}>
                        <span role={"button"} onClick={prevMonth} className={"button"}><PrevIcon/>Previous</span>
                    </th>
                    <th key={`current-month`} colSpan={count}
                        style={{textAlign: "center"}}>{months[currentDate.getMonth()]} {currentDate.getFullYear()}</th>
                    <th key={`next-month`}>
                        <span role={"button"} onClick={nextMonth} className={"button"}>Next<NextIcon/></span>
                    </th>
                </tr>
                <tr>
                    <th key={`coworkers`}></th>
                    {labels.map(item => (<th>{item}</th>))}
                    <th key={`total`}></th>
                </tr>
                </thead>
                <tbody>
                {data.map((item, index) => {
                    item.total = 0;
                    return (
                        <tr key={`coworker-${index}`} data-key={item.id}>
                            <td>{item.name}</td>
                            {[...Array(count)].map((dataItem, index) => {
                                const date = `${currentDate.getFullYear()}-${("0" + (currentDate.getMonth() + 1)).slice(-2)}-${index + 1}`;
                                const today = new Date();
                                const el = findByDate(item.data, date);
                                if (today > Date.parse(date)) {
                                    if (el) {
                                        item.total += el.count;
                                        return (
                                            <td className={"calendar isset"} onClick={showModal}
                                                data-date={date}>{el.count}</td>)
                                    } else {
                                        return (<td className={"calendar empty"} onClick={showModal}
                                                    data-date={date}>0</td>)
                                    }
                                } else {
                                    return (<td onClick={showModal} data-date={date} className={"calendar"}></td>)
                                }
                            })}
                            <td style={{textAlign: "center"}} width={120} className={"total"}>{item.total}</td>
                        </tr>
                    )
                })}
                </tbody>
                <tfoot>
                <tr>
                    <td colSpan={count - 1}></td>
                    <td colSpan={2} style={{textAlign: "center"}}>Итого:</td>
                    <td style={{textAlign: "center"}}>{summary}</td>
                </tr>
                </tfoot>
            </table>
        </>
    )
}