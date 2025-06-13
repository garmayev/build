export const Left = ({size = 24, color = "#5f6368"}: {
    size: number,
    color: string
}) => (
    <svg xmlns="http://www.w3.org/2000/svg" height={`${size}px`} viewBox="0 -960 960 960" width="24px" fill={color}>
        <path d="M560-240 320-480l240-240 56 56-184 184 184 184-56 56Z"/>
    </svg>
)

export const Right = ({size = 24, color = "#5f6368"}: {
    size: number,
    color: string
}) => (
    <svg xmlns="http://www.w3.org/2000/svg" height={`${size}px`} viewBox="0 -960 960 960" width="24px" fill={color}>
        <path d="M504-480 320-664l56-56 240 240-240 240-56-56 184-184Z"/>
    </svg>
)