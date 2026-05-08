@vite(['resources/js/app.js', 'resources/js/layout.js'])

<script>
    window.showToast = window.showToast || function (message, type = 'success') {
        const classes = {
            success: 'bg-success',
            error: 'bg-danger',
            info: 'bg-info',
            warning: 'bg-warning',
        };

        Toastify({
            text: message,
            duration: 4000,
            close: true,
            gravity: 'top',
            position: 'right',
            className: classes[type] || type || 'bg-success',
        }).showToast();
    };
</script>

@php
    $toasts = [];

    foreach ([
        'toast-success' => 'bg-success',
        'toast-error' => 'bg-danger',
        'toast-warning' => 'bg-warning',
        'toast-info' => 'bg-info',
    ] as $key => $className) {
        if (session()->has($key)) {
            $toasts[] = [
                'text' => session($key),
                'className' => $className,
            ];
        }
    }

    if (session()->has('status')) {
        $toasts[] = [
            'text' => session('status'),
            'className' => 'bg-info',
        ];
    }
@endphp

@if(! empty($toasts))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toasts = @json($toasts);

        toasts.forEach((toast) => {
            Toastify({
                text: toast.text,
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                className: toast.className,
            }).showToast();
        });
    });
</script>
@endif

@yield('scripts')
