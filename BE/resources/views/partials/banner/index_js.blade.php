<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    // Xác nhận xóa
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const name = this.dataset.name;
                if (confirm(`Bạn có chắc chắn muốn xóa "${name}"?`)) {
                    this.closest('form').submit();
                }
            });
        });

        // Sắp xếp banner
        const sortableList = document.getElementById('sortable-banners');
        if (sortableList && sortableList.children.length > 1) {
            new Sortable(sortableList, {
                animation: 150,
                handle: '.handle',
                ghostClass: 'sortable-ghost',
                onEnd: function() {
                    updateBannerPositions();
                }
            });
        }
    });

    // Cập nhật vị trí banner
    function updateBannerPositions() {
        const rows = document.querySelectorAll('#sortable-banners tr');
        const positions = [];
        
        rows.forEach((row, index) => {
            positions.push({
                id: row.dataset.id,
                position: index + 1
            });
        });

        fetch('{{ route("banners.positions") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ positions: positions })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Vị trí banner đã được cập nhật');
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
        });
    }
</script> 