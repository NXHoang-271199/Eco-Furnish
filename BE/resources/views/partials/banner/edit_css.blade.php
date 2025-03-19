<style>
    .preview-container {
        width: 100%;
        max-width: 100%;
        height: 200px;
        border: 2px dashed #ddd;
        border-radius: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
        overflow: hidden;
        position: relative;
    }
    
    .preview-container.has-image {
        border: none;
    }
    
    .preview-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    
    .preview-container .placeholder {
        color: #aaa;
        text-align: center;
    }
    
    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 255, 255, 0.8);
        color: #dc3545;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .preview-container:hover .remove-image {
        opacity: 1;
    }
    
    /* Toggle Switch Styling */
    .form-switch.form-switch-success .form-check-input {
        width: 50px;
        height: 24px;
        background-color: #e9e9ef;
        border-color: #e9e9ef;
    }
    
    .form-switch.form-switch-success .form-check-input:checked {
        background-color: #0ac074;
        border-color: #0ac074;
    }
    
    .form-switch .form-check-input {
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .form-switch .form-check-label {
        margin-left: 10px;
        font-weight: 500;
        cursor: pointer;
    }
    
    .form-switch .switch-icon-left,
    .form-switch .switch-icon-right {
        position: absolute;
        top: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        height: 16px;
        width: 16px;
        transition: all 0.3s ease;
    }
    
    .form-switch .switch-icon-left {
        left: 7px;
        opacity: 0;
    }
    
    .form-switch .switch-icon-right {
        right: 7px;
    }
    
    .form-switch .form-check-input:checked + .form-check-label .switch-icon-left {
        opacity: 1;
    }
    
    .form-switch .form-check-input:checked + .form-check-label .switch-icon-right {
        opacity: 0;
    }

    /* Modern Toggle Switch */
    .toggle-switch-modern {
        display: flex;
        align-items: center;
    }

    .toggle-switch-modern .toggle-label {
        margin-right: 10px;
        font-weight: 600;
        color: #495057;
    }

    .toggle-switch-modern .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }

    .toggle-switch-modern .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-switch-modern .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e9ecef;
        transition: .4s;
        border-radius: 30px;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }

    .toggle-switch-modern .slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .toggle-switch-modern input:checked + .slider {
        background-color: #0ac074;
    }

    .toggle-switch-modern input:focus + .slider {
        box-shadow: 0 0 1px #0ac074;
    }

    .toggle-switch-modern input:checked + .slider:before {
        transform: translateX(30px);
    }

    .toggle-switch-modern .slider .icons {
        display: flex;
        justify-content: space-between;
        padding: 5px 8px;
        color: white;
    }

    .toggle-switch-modern .slider .icon-on,
    .toggle-switch-modern .slider .icon-off {
        font-size: 14px;
        transition: .4s;
    }

    .toggle-switch-modern .slider .icon-on {
        opacity: 0;
    }

    .toggle-switch-modern .slider .icon-off {
        opacity: 1;
    }

    .toggle-switch-modern input:checked + .slider .icon-on {
        opacity: 1;
    }

    .toggle-switch-modern input:checked + .slider .icon-off {
        opacity: 0;
    }

    .toggle-switch-modern .status-text {
        margin-left: 10px;
        font-weight: 500;
        transition: .3s;
    }

    .toggle-switch-modern .status-on {
        color: #0ac074;
        display: none;
    }

    .toggle-switch-modern .status-off {
        color: #6c757d;
        display: inline-block;
    }

    .toggle-switch-modern input:checked ~ .status-on {
        display: inline-block;
    }

    .toggle-switch-modern input:checked ~ .status-off {
        display: none;
    }
</style> 