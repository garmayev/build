import {createContext, ReactNode, useContext, useState} from "react";


// @ts-ignore
export const CalendarContext = createContext();

export const CalendarProvider = ({children}: {
    children: ReactNode
}) => {
    const [date, setDate] = useState(new Date())
    const [month, setMonth] = useState(date.getMonth())
    const [year, setYear] = useState(date.getFullYear())
    const [coworkerId, setCoworkerId] = useState(null)
    const prevMonth = () => {
        const nextDate = new Date();
        if (month > 0) {
            setMonth(month - 1)
            nextDate.setMonth(month - 1)
        } else {
            setMonth(11)
            setYear(year - 1)
            nextDate.setMonth(11)
            nextDate.setFullYear(year - 1)
        }
        setDate(nextDate)
    }
    const nextMonth = () => {
        const nextDate = new Date();
        if (month > 10) {
            setMonth(0)
            setYear(year + 1);
            nextDate.setMonth(0)
            nextDate.setFullYear(year + 1)
        } else {
            setMonth(month + 1)
            nextDate.setMonth(month + 1)
        }
        setDate(nextDate)
    }
    return (
        <CalendarContext.Provider value={{
            date: date,
            month: month,
            year: year,
            setMonth: setMonth,
            setYear: setYear,
            nextMonth: nextMonth,
            prevMonth: prevMonth,
            coworker: {
                id: coworkerId,
                setId: setCoworkerId
            },
        }}>
            {children}
        </CalendarContext.Provider>
    )
}

export const useCalendarContext = () => useContext(CalendarContext)