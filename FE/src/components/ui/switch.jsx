import * as React from "react"
import { cn } from "../../lib/utils"

const Switch = React.forwardRef(
    ({ className, ...props }, ref) => (
        <div className="relative inline-flex h-[24px] w-[44px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background data-[state=checked]:bg-primary data-[state=unchecked]:bg-input"
            {...props}
            ref={ref}
        >
            <span
                aria-hidden="true"
                className={cn(
                    "pointer-events-none inline-block h-[20px] w-[20px] rounded-full bg-background shadow-lg ring-0 transition-transform duration-200 ease-in-out data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0",
                    className
                )}
            />
        </div>
    )
)
Switch.displayName = "Switch"

export { Switch } 