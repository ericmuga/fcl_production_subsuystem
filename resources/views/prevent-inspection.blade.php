{{-- resources/views/disable-inspection.blade.php --}}
<script>
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    document.onkeydown = function(e) {
        if (e.keyCode == 123 || (e.ctrlKey && e.shiftKey && e.code == 'KeyI') || 
            (e.ctrlKey && e.shiftKey && e.code == 'KeyJ') || 
            (e.ctrlKey && e.shiftKey && e.code == 'KeyC') || 
            (e.ctrlKey && e.code == 'KeyU')) {
            e.preventDefault();
        }
    };
</script>
