window.onload = function () {
    const loginButton = document.querySelector('#login_to_salesforce');
    const revokeButton = document.querySelector('#revoke_salesforce');
    if (loginButton != null) {
        loginButton.addEventListener('click', function (e) {
            e.preventDefault();

            let data = new FormData();
            data.append('action', 'login_to_salesforce');
            data.append('_ajax_nonce', wp_ajax_obj.nonce);
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: data
            })
                .then(response => response.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error("WP Error: " + response.data);
                    }
                    console.log(response.data);
                    window.location.href = response.data
                })
                .catch(error => {
                    console.log('Request failed', error);
                });
        });
    }
    if (revokeButton != null) {
        revokeButton.addEventListener('click', function (e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to revoke the token?')) {
                return;
            }

            let data = new FormData();
            data.append('action', 'revoke_token');
            data.append('_ajax_nonce', wp_ajax_obj.nonce);

            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: data
            })
                .then(response => response.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error("WP Error: " + response.data);
                    }
                    location.reload();
                })
                .catch(error => {
                    console.log('Request failed', error);
                });
        });
    }

    const selectElements = document.querySelectorAll('select');
    selectElements.forEach(function (selectElement) {
        selectElement.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const optgroupLabel = selectedOption.parentNode.label;
            const parentSelect = this.parentNode;

            // Remove existing appended label if exists
            let existingLabel = parentSelect.querySelector('.appended-label');
            if (existingLabel) {
                existingLabel.remove();
            }

            // Append new label
            const label = document.createElement('label');
            label.className = 'appended-label';
            label.textContent = optgroupLabel;
            parentSelect.appendChild(label);
        });

        // Trigger change event
        const event = new Event('change');
        selectElement.dispatchEvent(event);
    });

    document.querySelector('.CF7SF__wrp form').addEventListener('submit', function(e) {
        const requiredOptions = document.querySelectorAll('option[data-require="true"]');
        let selectedRequired = {};

        for (let i = 0; i < requiredOptions.length; i++) {
            let optionValue = requiredOptions[i].value;
            selectedRequired[optionValue] = false;
        }

        const selectedOptions = document.querySelectorAll('select option:checked');
        for (let i = 0; i < selectedOptions.length; i++) {
            let optionValue = selectedOptions[i].value;
            if (selectedRequired.hasOwnProperty(optionValue)) {
                selectedRequired[optionValue] = true;
            }
        }

        for (let key in selectedRequired) {
            if (selectedRequired[key] !== true) {

                e.preventDefault();
                alert('Please select all required options!');
                return;
            }
        }

    });

}

