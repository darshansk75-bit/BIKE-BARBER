/**
 * Admin Export Handler
 * Captures UI filters and redirects to export.php
 */
document.addEventListener('DOMContentLoaded', function() {
    const exportTriggers = document.querySelectorAll('.export-trigger');
    
    exportTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            
            const module = this.getAttribute('data-module');
            const type = this.getAttribute('data-type');
            
            // Collect Filters from UI
            let filters = {
                module: module,
                type: type
            };

            // Module specific filter gathering
            if (module === 'orders') {
                const search = document.getElementById('orderSearch');
                const status = document.getElementById('statusFilter');
                if (search) filters.search = search.value;
                if (status) filters.status = status.value;
            } 
            else if (module === 'wallet') {
                // For wallet, we might have a search or tab-based filter
                const search = document.getElementById('walletSearch'); // If exists
                const activeTab = document.querySelector('.data-tab.active');
                if (search) filters.search = search.value;
                if (activeTab) {
                    const target = activeTab.getAttribute('data-target');
                    if (target === 'credits') filters.status = 'CREDIT';
                    else if (target === 'debits') filters.status = 'EXPENSE';
                }
            } 
            else if (module === 'analytics') {
                // Date range from dashboard
                const dateText = document.getElementById('dashboardDateText');
                if (dateText) {
                    // Sample: "Mar 01 - Mar 18"
                    // In a real app, we'd read from the flatpickr instance
                    // But for now we'll check if flatpickr exists
                    const picker = document.querySelector('#dashboardDatePicker');
                    if (picker && picker._flatpickr) {
                        const dates = picker._flatpickr.selectedDates;
                        if (dates.length === 2) {
                            filters.from_date = formatSQLDate(dates[0]);
                            filters.to_date = formatSQLDate(dates[1]);
                        }
                    }
                }
            }

            // Construct Query String
            const queryString = Object.keys(filters)
                .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(filters[key]))
                .join('&');

            // Redirect to Export
            window.location.href = 'export.php?' + queryString;
        });
    });

    function formatSQLDate(date) {
        let d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }
});
