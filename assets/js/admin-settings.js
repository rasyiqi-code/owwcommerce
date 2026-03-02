/**
 * OwwCommerce Admin Settings Tabs v2
 */
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.owwc-tab-item');
    const contents = document.querySelectorAll('.owwc-tab-content');

    if (tabs.length === 0) return;

    function switchTab(target) {
        // Update Tabs UI
        tabs.forEach(t => {
            if (t.getAttribute('data-tab') === target) {
                t.classList.add('active');
            } else {
                t.classList.remove('active');
            }
        });

        // Update Content UI
        contents.forEach(content => {
            if (content.id === 'tab-' + target) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });

        // Update URL Hash without jumping
        if (history.pushState) {
            history.pushState(null, null, '#' + target);
        } else {
            location.hash = target;
        }
    }

    // Handle Tab Click
    tabs.forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            const target = this.getAttribute('data-tab');
            switchTab(target);
        });
    });

    // Handle Initial Tab from Hash
    const hash = window.location.hash.substring(1);
    const validTabs = Array.from(tabs).map(t => t.getAttribute('data-tab'));

    if (hash && validTabs.includes(hash)) {
        switchTab(hash);
    } else if (validTabs.length > 0) {
        // Default to first tab if no valid hash
        switchTab(validTabs[0]);
    }
});
