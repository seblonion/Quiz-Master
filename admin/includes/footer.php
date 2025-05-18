            <!-- End of content -->
            </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle
        const menuToggle = document.createElement('button');
        menuToggle.className = 'menu-toggle';
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        menuToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('open');
        });
        
        document.querySelector('.top-bar').prepend(menuToggle);
    });
    </script>
</body>
</html>