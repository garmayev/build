import {AriaAttributes, DataHTMLAttributes, DOMAttributes} from "react";

interface HTMLAttributes<T> extends AriaAttributes, DOMAttributes<T>, DataHTMLAttributes<T> {
    url: string
}
interface CalendarProps extends HTMLAttributes<HTMLElement>{
}
