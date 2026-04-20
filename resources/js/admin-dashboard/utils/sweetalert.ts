import Swal from 'sweetalert2';

/**
 * SweetAlert Utility
 * Provides standardized, premium configurations for different alert types.
 */

export const colors = {
    primary: '#526D82', // Matching Publish Article Button
    danger: '#e11d48',
    success: '#10b981',
    warning: '#f59e0b',
    info: '#526D82',    
    background: '#ffffff',
    text: '#1e293b'
};

export const commonConfig = {
    confirmButtonColor: colors.primary,
    cancelButtonColor: '#94a3b8',
    reverseButtons: true,
    buttonsStyling: true,
    customClass: {
        confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm',
        cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm',
        denyButton: 'px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm',
        popup: 'rounded-2xl border-none shadow-2xl',
        title: 'text-slate-800 font-bold',
        htmlContainer: 'text-slate-500'
    }
};

export const toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

export const alertService = {
    /**
     * Show a simple success toast
     */
    successToast(title: string) {
        return toast.fire({
            icon: 'success',
            title,
            background: '#ecfdf5',
            color: '#065f46',
            iconColor: '#10b981'
        });
    },

    /**
     * Show a simple info toast (for instructions/steps)
     */
    infoToast(title: string) {
        return toast.fire({
            icon: 'info',
            title,
            background: '#eff6ff',
            color: '#1e40af',
            iconColor: '#3b82f6'
        });
    },

    /**
     * Show a simple error toast
     */
    errorToast(title: string, text?: string) {
        return toast.fire({
            icon: 'error',
            title,
            text,
            background: '#fff1f2',
            color: '#9f1239',
            iconColor: '#e11d48'
        });
    },

    /**
     * Show a confirmation dialog (Delete, etc)
     */
    async confirm(options: { 
        title: string, 
        text?: string, 
        icon?: 'warning' | 'error' | 'question' | 'info' | 'success',
        confirmButtonText?: string,
        cancelButtonText?: string,
        danger?: boolean
    }) {
        return Swal.fire({
            ...commonConfig,
            title: options.title,
            text: options.text,
            icon: options.icon || 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText || 'Confirm',
            cancelButtonText: options.cancelButtonText || 'Cancel',
            confirmButtonColor: options.danger ? colors.danger : colors.primary,
        });
    },

    /**
     * Show an info modal with HTML support
     */
    info(title: string, html: string) {
        return Swal.fire({
            ...commonConfig,
            title,
            html,
            icon: 'info',
            width: '800px', // Perlebar modal
        });
    },

    /**
     * Show a simple alert modal
     */
    alert(titleOrOptions: string | { title: string, text?: string, icon?: any }, text?: string, icon: any = 'info') {
        if (typeof titleOrOptions === 'object') {
            return Swal.fire({
                ...commonConfig,
                title: titleOrOptions.title,
                text: titleOrOptions.text,
                icon: titleOrOptions.icon || 'info',
            });
        }

        return Swal.fire({
            ...commonConfig,
            title: titleOrOptions,
            text,
            icon,
        });
    }
};

export { Swal };
