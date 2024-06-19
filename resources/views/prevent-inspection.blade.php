{{-- resources/views/disable-inspection.blade.php --}}
<script>
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });

    document.onkeydown = function(e) {
        if (e.keyCode == 123 || 
            (e.ctrlKey && e.shiftKey && (e.code == 'KeyI' || e.code == 'KeyJ' || e.code == 'KeyC')) || 
            (e.ctrlKey && e.code == 'KeyU')) {
            e.preventDefault();
        }
    };

    function disablePage() {
        console.log('Disabling page');
        document.body.style.overflow = 'hidden';
        const overlay = document.createElement('div');
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.8)';
        overlay.style.color = 'white';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.zIndex = '9999';
        overlay.innerHTML = '<h1>Developer tools are open. Please close them to use the application.</h1>';
        document.body.appendChild(overlay);
    }

    function detectDevTools() {
        console.log('Checking for dev tools');

        // Check based on dimensions
        if (window.outerWidth - window.innerWidth > 160 || window.outerHeight - window.innerHeight > 160) {
            disablePage();
            return;
        }

        // Check based on timing
        const start = new Date();
        debugger;
        const end = new Date();
        if (end - start > 100) {
            disablePage();
            return;
        }
    }

    window.addEventListener('resize', detectDevTools);
    setInterval(detectDevTools, 1000);
    detectDevTools();
</script>
