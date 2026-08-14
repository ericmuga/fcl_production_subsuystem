if (!window.highcareScaleHelper) {
    window.highcareScaleHelper = {
        resolveHost(ip) {
            return ip && String(ip).trim() !== '' ? String(ip).trim() : 'localhost';
        },

        updateHostBadge(badgeSelector, ip) {
            const badge = document.querySelector(badgeSelector);
            if (!badge) {
                return;
            }

            const host = this.resolveHost(ip);
            badge.textContent = host;
            badge.classList.remove('badge-secondary', 'badge-info');
            badge.classList.add(host === 'localhost' ? 'badge-secondary' : 'badge-info');
        },

        getWeight(options) {
            const {
                ip,
                comport,
                endpointPath,
                buttonId,
                errorSelector,
                onSuccess,
            } = options;

            const button = document.getElementById(buttonId);
            const errorBox = document.querySelector(errorSelector);

            if (!comport) {
                alert('Scale COM port is not configured.');
                return;
            }

            const host = this.resolveHost(ip);
            const fullUrl = 'http://' + host + endpointPath + '/' + encodeURIComponent(comport);

            if (errorBox) {
                errorBox.innerHTML = '';
            }

            if (!button) {
                return;
            }

            button.disabled = true;
            const originalLabel = button.innerHTML;
            button.innerHTML = '<strong>Reading...</strong>';

            const source = axios.CancelToken.source();
            const timeoutId = setTimeout(() => {
                source.cancel('No response received from scale');
            }, 5000);

            axios.get(fullUrl, { cancelToken: source.token })
                .then((response) => {
                    clearTimeout(timeoutId);

                    if (response.data && response.data.success) {
                        const value = parseFloat(response.data.response);
                        if (Number.isFinite(value)) {
                            onSuccess(value);
                        } else if (errorBox) {
                            errorBox.innerHTML = '<div class="alert alert-danger small-alert mb-0">Invalid scale response.</div>';
                        }
                    } else if (errorBox) {
                        errorBox.innerHTML = '<div class="alert alert-danger small-alert mb-0">API call was not successful.</div>';
                    }
                })
                .catch((error) => {
                    clearTimeout(timeoutId);
                    const message = axios.isCancel(error)
                        ? error.message
                        : 'Error on request: ' + error.message;

                    if (errorBox) {
                        errorBox.innerHTML = '<div class="alert alert-danger small-alert mb-0">' + message + '</div>';
                    }
                })
                .finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalLabel;
                });
        }
    };
}
