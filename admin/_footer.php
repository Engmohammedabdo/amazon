        </main>
    </div>

    <script>
        // Tab switching functionality
        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }

        // Confirm delete
        function confirmDelete(message) {
            return confirm(message || 'هل أنت متأكد من الحذف؟');
        }

        // Show/hide API key
        function toggleApiKey() {
            const keyField = document.getElementById('apiKeyValue');
            const btn = document.getElementById('toggleBtn');

            if (keyField.type === 'password') {
                keyField.type = 'text';
                btn.textContent = '🔒 إخفاء';
            } else {
                keyField.type = 'password';
                btn.textContent = '👁️ إظهار';
            }
        }

        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('تم النسخ!');
            });
        }
    </script>
</body>
</html>
