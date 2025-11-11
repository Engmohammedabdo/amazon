/**
 * Search Autocomplete
 * Shows dropdown with suggestions as user types
 */

(function() {
    'use strict';

    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    // Create dropdown element
    const dropdown = document.createElement('div');
    dropdown.className = 'search-autocomplete-dropdown';
    dropdown.style.display = 'none';

    // Insert dropdown after search input wrapper
    const searchWrapper = searchInput.closest('.search-input-wrapper') || searchInput.parentElement;
    if (searchWrapper) {
        searchWrapper.style.position = 'relative';
        searchWrapper.appendChild(dropdown);
    }

    let debounceTimer;
    let currentHighlight = -1;
    let suggestions = [];

    // Fetch suggestions from API
    function fetchSuggestions(query) {
        if (query.length < 2) {
            hideDropdown();
            return;
        }

        fetch(`/api/search_suggestions.php?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                suggestions = data;
                displaySuggestions(data);
            })
            .catch(error => {
                console.error('Autocomplete error:', error);
                hideDropdown();
            });
    }

    // Display suggestions in dropdown
    function displaySuggestions(items) {
        if (!items || items.length === 0) {
            hideDropdown();
            return;
        }

        dropdown.innerHTML = items.map((item, index) => `
            <div class="autocomplete-item" data-index="${index}" data-term="${item.term}">
                <span class="autocomplete-icon">${item.icon}</span>
                <span class="autocomplete-term">${item.term}</span>
                <span class="autocomplete-count">(${item.count})</span>
            </div>
        `).join('');

        showDropdown();
        attachItemListeners();
    }

    // Show dropdown
    function showDropdown() {
        dropdown.style.display = 'block';
        currentHighlight = -1;
    }

    // Hide dropdown
    function hideDropdown() {
        dropdown.style.display = 'none';
        currentHighlight = -1;
    }

    // Attach click listeners to items
    function attachItemListeners() {
        const items = dropdown.querySelectorAll('.autocomplete-item');
        items.forEach(item => {
            item.addEventListener('click', function() {
                const term = this.getAttribute('data-term');
                selectSuggestion(term);
            });

            item.addEventListener('mouseenter', function() {
                removeAllHighlights();
                this.classList.add('highlighted');
            });
        });
    }

    // Select a suggestion
    function selectSuggestion(term) {
        searchInput.value = term;
        hideDropdown();
        // Trigger search
        if (typeof updateFilters === 'function') {
            updateFilters();
        }
    }

    // Remove all highlights
    function removeAllHighlights() {
        const items = dropdown.querySelectorAll('.autocomplete-item');
        items.forEach(item => item.classList.remove('highlighted'));
    }

    // Highlight item by index
    function highlightItem(index) {
        removeAllHighlights();
        const items = dropdown.querySelectorAll('.autocomplete-item');

        if (index >= 0 && index < items.length) {
            items[index].classList.add('highlighted');
            currentHighlight = index;

            // Scroll into view if needed
            items[index].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }

    // Input event with debounce
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const items = dropdown.querySelectorAll('.autocomplete-item');

        if (dropdown.style.display === 'none' || items.length === 0) {
            return;
        }

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                currentHighlight = Math.min(currentHighlight + 1, items.length - 1);
                highlightItem(currentHighlight);
                break;

            case 'ArrowUp':
                e.preventDefault();
                currentHighlight = Math.max(currentHighlight - 1, 0);
                highlightItem(currentHighlight);
                break;

            case 'Enter':
                if (currentHighlight >= 0) {
                    e.preventDefault();
                    const term = items[currentHighlight].getAttribute('data-term');
                    selectSuggestion(term);
                }
                break;

            case 'Escape':
                e.preventDefault();
                hideDropdown();
                break;
        }
    });

    // Focus event
    searchInput.addEventListener('focus', function() {
        if (suggestions.length > 0 && this.value.trim().length >= 2) {
            showDropdown();
        }
    });

    // Click outside to close
    document.addEventListener('click', function(e) {
        if (!searchWrapper.contains(e.target)) {
            hideDropdown();
        }
    });

})();
