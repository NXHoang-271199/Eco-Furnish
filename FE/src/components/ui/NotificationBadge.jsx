import React from 'react';

const NotificationBadge = ({ count }) => {
    if (!count || count < 1) return null;

    return (
        <span className="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-medium">
            {count > 99 ? '99+' : count}
        </span>
    );
};

export default NotificationBadge; 