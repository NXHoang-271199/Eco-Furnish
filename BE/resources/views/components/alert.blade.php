<<<<<<< HEAD
<div>
=======
@if (session('success') || session('error'))
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if(session('success'))
                Toastify({
                    text: "{{ session('success') }}",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
<<<<<<< HEAD
                    className: "bg-success",
                    style: {
                        background: "var(--vz-success)",
                        color: "#fff",
                        boxShadow: "0 10px 20px -10px var(--vz-success)"
=======
                    className: "bg-primary",
                    style: {
                        background: "var(--vz-primary)",
                        color: "#fff",
                        boxShadow: "0 10px 20px -10px var(--vz-primary)"
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
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
<<<<<<< HEAD
                        color: "#fff",
=======
                        color: "#fff", 
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
                        boxShadow: "0 10px 20px -10px var(--vz-danger)"
                    }
                }).showToast();
            @endif
        });
    </script>
<<<<<<< HEAD
</div>
=======
@endif
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
