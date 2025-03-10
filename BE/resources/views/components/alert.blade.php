@if (session('success') || session('error'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if(session('success'))
                Toastify({
                    text: "{{ session('success') }}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    className: "bg-success",
                    style: {
                        background: "var(--vz-success)",
                        color: "#fff",
                        boxShadow: "0 10px 20px -10px var(--vz-success)"
                    }
                }).showToast();
            @endif

            @if(session('error'))
                Toastify({
                    text: "{{ session('error') }}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    className: "bg-danger",
                    style: {
                        background: "var(--vz-danger)",
                        color: "#fff",
                        boxShadow: "0 10px 20px -10px var(--vz-danger)"
                    }
                }).showToast();
            @endif
        });
    </script>
@endif
