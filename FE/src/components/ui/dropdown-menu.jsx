import * as React from "react"
import { cn } from "../../lib/utils"

const DropdownMenu = ({ children }) => {
    return <div className="relative inline-block text-left">{children}</div>
}

const DropdownMenuTrigger = ({ asChild, children, ...props }) => {
    return (
        <div {...props}>
            {children}
        </div>
    )
}

const DropdownMenuContent = React.forwardRef(
    ({ className, align = "center", ...props }, ref) => (
        <div
            ref={ref}
            className={cn(
                "absolute z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md animate-in data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2",
                {
                    "left-0": align === "start",
                    "right-0": align === "end",
                    "left-1/2 -translate-x-1/2": align === "center",
                },
                className
            )}
            {...props}
        />
    )
)

const DropdownMenuItem = React.forwardRef(
    ({ className, ...props }, ref) => (
        <div
            ref={ref}
            className={cn(
                "relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors focus:bg-accent focus:text-accent-foreground hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50",
                className
            )}
            {...props}
        />
    )
)

const DropdownMenuSeparator = React.forwardRef(
    ({ className, ...props }, ref) => (
        <div
            ref={ref}
            className={cn("-mx-1 my-1 h-px bg-muted", className)}
            {...props}
        />
    )
)

const DropdownMenuLabel = React.forwardRef(
    ({ className, ...props }, ref) => (
        <div
            ref={ref}
            className={cn("px-2 py-1.5 text-sm font-semibold", className)}
            {...props}
        />
    )
)

const DropdownMenuGroup = React.forwardRef(
    ({ className, ...props }, ref) => (
        <div
            ref={ref}
            className={cn("p-1", className)}
            {...props}
        />
    )
)

DropdownMenu.displayName = "DropdownMenu"
DropdownMenuTrigger.displayName = "DropdownMenuTrigger"
DropdownMenuContent.displayName = "DropdownMenuContent"
DropdownMenuItem.displayName = "DropdownMenuItem"
DropdownMenuSeparator.displayName = "DropdownMenuSeparator"
DropdownMenuLabel.displayName = "DropdownMenuLabel"
DropdownMenuGroup.displayName = "DropdownMenuGroup"

export {
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuLabel,
    DropdownMenuGroup
} 