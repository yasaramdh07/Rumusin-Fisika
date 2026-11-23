// Panel chat Ruka pada halaman materi (/materi/{slug}).
function initRukaChat() {
    const widget = document.querySelector('[data-ruka-widget]');
    if (!widget) return;

    const toggleBtn = widget.querySelector('[data-ruka-toggle]');
    const closeBtn = widget.querySelector('[data-ruka-close]');
    const panel = widget.querySelector('[data-ruka-panel]');
    const messagesBox = widget.querySelector('[data-ruka-messages]');
    const typingIndicator = widget.querySelector('[data-ruka-typing]');
    const errorBox = widget.querySelector('[data-ruka-error]');
    const errorText = widget.querySelector('[data-ruka-error-text]');
    const limitBanner = widget.querySelector('[data-ruka-limit-banner]');
    const remainingNode = widget.querySelector('[data-ruka-remaining]');
    const form = widget.querySelector('[data-ruka-form]');
    const input = widget.querySelector('[data-ruka-input]');
    const submitBtn = widget.querySelector('[data-ruka-submit]');
    const charCount = widget.querySelector('[data-ruka-char-count]');
    const clearBtn = widget.querySelector('[data-ruka-clear]');

    if (!toggleBtn || !panel) return;

    const scrollToBottom = () => {
        if (messagesBox) {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }
    };

    const setOpen = (open) => {
        if (open) {
            panel.removeAttribute('hidden');
            toggleBtn.setAttribute('aria-expanded', 'true');
            scrollToBottom();
            input?.focus();
        } else {
            panel.setAttribute('hidden', 'hidden');
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.focus();
        }
    };

    toggleBtn.addEventListener('click', () => {
        const isHidden = panel.hasAttribute('hidden');
        setOpen(isHidden);
    });

    closeBtn?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hasAttribute('hidden')) {
            setOpen(false);
        }
    });

    if (input && charCount) {
        const updateChars = () => {
            charCount.textContent = String(input.value.length);
        };
        input.addEventListener('input', updateChars);
        updateChars();
    }

    const createBubbleNode = (role, htmlContent) => {
        const item = document.createElement('div');
        item.className = `rf-chat__item rf-chat__item--${role}`;
        item.setAttribute('data-ruka-history-item', '');

        if (role === 'assistant') {
            const avatar = document.createElement('span');
            avatar.className = 'rf-ruka-avatar rf-ruka-avatar--sm';
            avatar.setAttribute('aria-hidden', 'true');
            avatar.textContent = 'R';
            item.appendChild(avatar);
        }

        const bubble = document.createElement('div');
        bubble.className =
            role === 'assistant'
                ? 'rf-chat__bubble rf-chat__bubble--assistant rf-lesson__prose'
                : 'rf-chat__bubble rf-chat__bubble--user';
        bubble.setAttribute('data-math-root', '');
        bubble.innerHTML = htmlContent;

        item.appendChild(bubble);
        return item;
    };

    const appendMessageNode = (node) => {
        if (!messagesBox) return;
        if (typingIndicator && typingIndicator.parentNode === messagesBox) {
            messagesBox.insertBefore(node, typingIndicator);
        } else {
            messagesBox.appendChild(node);
        }
    };

    const updateQuotaState = (remaining) => {
        if (typeof remaining !== 'number') return;
        if (remainingNode) {
            remainingNode.textContent = String(remaining);
        }

        const exhausted = remaining <= 0;
        if (limitBanner) {
            if (exhausted) {
                limitBanner.removeAttribute('hidden');
            } else {
                limitBanner.setAttribute('hidden', 'hidden');
            }
        }
        if (input) input.disabled = exhausted;
        if (submitBtn) submitBtn.disabled = exhausted;
    };

    if (form && input && submitBtn) {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const message = input.value.trim();
            if (!message) return;

            errorBox?.setAttribute('hidden', 'hidden');
            typingIndicator?.removeAttribute('hidden');
            submitBtn.disabled = true;
            input.disabled = true;
            scrollToBottom();

            const csrfToken = form.querySelector('input[name="_token"]')?.value || '';

            try {
                const response = await window.fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ message }),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (data.limit_reached) {
                        updateQuotaState(0);
                    }
                    const errMessage = data.message || 'Ruka lagi nggak bisa jawab. Coba lagi sebentar.';
                    if (errorText) errorText.textContent = errMessage;
                    errorBox?.removeAttribute('hidden');
                    return;
                }

                input.value = '';
                if (charCount) charCount.textContent = '0';

                if (data.user_message?.html) {
                    appendMessageNode(createBubbleNode('user', data.user_message.html));
                }
                if (data.assistant_message?.html) {
                    appendMessageNode(createBubbleNode('assistant', data.assistant_message.html));
                }

                if (typeof window.rfRenderMath === 'function' && messagesBox) {
                    await window.rfRenderMath(messagesBox);
                }

                updateQuotaState(data.remaining_today);
                scrollToBottom();
            } catch {
                if (errorText) {
                    errorText.textContent = 'Ruka lagi nggak bisa jawab. Coba lagi sebentar.';
                }
                errorBox?.removeAttribute('hidden');
            } finally {
                typingIndicator?.setAttribute('hidden', 'hidden');
                const currentRemaining = remainingNode ? parseInt(remainingNode.textContent || '1', 10) : 1;
                if (currentRemaining > 0) {
                    submitBtn.disabled = false;
                    input.disabled = false;
                    input.focus();
                }
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', async () => {
            const clearUrl = clearBtn.getAttribute('data-clear-url');
            if (!clearUrl) return;
            if (!window.confirm('Hapus seluruh riwayat percakapan dengan Ruka di materi ini?')) return;

            const csrfToken = form?.querySelector('input[name="_token"]')?.value || '';

            try {
                const response = await window.fetch(clearUrl, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                if (!response.ok) return;

                widget.querySelectorAll('[data-ruka-history-item]').forEach((node) => node.remove());
                if (typeof window.rfToast === 'function') {
                    window.rfToast('Percakapan dengan Ruka dihapus.', 'ok');
                }
            } catch {
                if (typeof window.rfToast === 'function') {
                    window.rfToast('Gagal menghapus percakapan.', 'error');
                }
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', initRukaChat);
