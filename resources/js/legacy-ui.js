const LegacyUI = (() => {
    let observer;

    const syncModalState = () => {
        const modalOpen = [...document.querySelectorAll('[data-ui-modal]')]
            .some((modal) => getComputedStyle(modal).display !== 'none');
        document.documentElement.classList.toggle('has-modal-open', modalOpen);
        document.body.classList.toggle('has-modal-open', modalOpen);
    };

    const enhance = (root = document) => {
        root.querySelectorAll?.('[data-ui-modal]:not([data-ui-ready])').forEach((modal) => {
            modal.dataset.uiReady = 'true';
            modal.dispatchEvent(new CustomEvent('ui:modal-opened', { bubbles: true }));
        });
        syncModalState();
    };

    const init = () => {
        enhance();
        observer?.disconnect();
        observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) enhance(node);
                });
            }
            syncModalState();
        });
        observer.observe(document.body, { attributes: true, childList: true, subtree: true });
    };

    const closeModal = (source, callback) => {
        const modal = source?.closest?.('[data-ui-modal]') ?? source;
        if (!modal) return callback?.();

        modal.classList.add('is-closing');
        const delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 180;
        window.setTimeout(() => {
            Promise.resolve(callback?.()).finally(() => {
                modal.classList.remove('is-closing');
                window.requestAnimationFrame(syncModalState);
            });
        }, delay);
    };

    const handleModalClose = (event) => {
        const trigger = event.target.closest?.('[data-ui-close]');
        if (!trigger) return;

        event.preventDefault();
        const modal = trigger.closest('[data-ui-modal]');
        const componentRoot = modal?.closest('[wire\\:id]');
        const componentId = componentRoot?.getAttribute('wire:id');
        const method = trigger.dataset.uiClose;

        if (!componentId || !method) return;
        closeModal(modal, () => window.Livewire?.find(componentId)?.call(method));
    };

    document.addEventListener('DOMContentLoaded', init, { once: true });
    document.addEventListener('click', handleModalClose);
    document.addEventListener('livewire:navigated', init);
    document.addEventListener('livewire:init', () => {
        window.Livewire?.hook('morph.updated', ({ el }) => enhance(el));
    });

    return { init, enhance, closeModal };
})();

window.LegacyUI = LegacyUI;
