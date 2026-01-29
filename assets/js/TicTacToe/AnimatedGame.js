import {html} from 'uhtml/node.js'

customElements.define('tic-tac-toe-animated-game', class extends HTMLElement {
    #plainElement; #moveElements; #abortController;

    connectedCallback() {
        this.#plainElement = html`<div class="gp-ttt-game__field"></div>`;
        this.#moveElements = Array.from(this.querySelectorAll('[data-move]')).sort((a, b) => {
            return parseInt(a.dataset.move) - parseInt(b.dataset.move);
        });
        this.#abortController = new AbortController();

        if (typeof AbortSignal?.timeout !== 'function' || typeof AbortSignal?.any !== 'function') {
            return;
        }

        this.addEventListener('mouseenter', this.#onMouseEnter);
        this.addEventListener('mouseleave', this.#onMouseLeave);
    }

    disconnectedCallback() {
        this.#abortController.abort();
    }

    #onMouseEnter = () => {
        this.#abortController = new AbortController();
        this.#runAnimation();
    }

    #onMouseLeave = () => {
        this.#abortController.abort();
        this.#showFinalState();
    }

    #runAnimation = async () => {
        this.#clear();

        for (const [i, moveElement] of this.#moveElements.entries()) {
            this.#moveElements.forEach(e => {
                e.classList.remove('gp-ttt-game__field--highlight', 'gp-ttt-game__field--current')
            });
            moveElement.classList.add('gp-ttt-game__field--highlight', 'gp-ttt-game__field--current');
            this.querySelector('[data-move="' + moveElement.dataset.move + '"]').replaceWith(moveElement);

            const isLastMove = i === this.#moveElements.length - 1;
            isLastMove && this.#showFinalState();

            try {
                await new Promise((resolve, reject) => {
                    AbortSignal.any([this.#abortController.signal, AbortSignal.timeout(isLastMove ? 1500 : 350)])
                        .addEventListener('abort', reject);
                });
            } catch {
                if (this.#abortController.signal.aborted) return;
            }
        }

        this.#runAnimation();
    }

    #clear() {
        this.#moveElements.forEach(e => {
            e.classList.remove('gp-ttt-game__field--highlight', 'gp-ttt-game__field--current');
            const clone = this.#plainElement.cloneNode();
            clone.dataset.move = e.dataset.move;
            if (e.hasAttribute('data-win')) clone.dataset.win = e.dataset.win;
            this.querySelector('[data-move="' + e.dataset.move + '"]').replaceWith(clone);
        });
    }

    #showFinalState() {
        this.#moveElements.forEach((e, k) => {
            e.classList.remove('gp-ttt-game__field--highlight', 'gp-ttt-game__field--current');
            k === this.#moveElements.length - 1 && e.classList.add('gp-ttt-game__field--current');
            e.hasAttribute('data-win') && e.classList.add('gp-ttt-game__field--highlight');
            this.querySelector('[data-move="' + e.dataset.move + '"]').replaceWith(e);
        });
    }
});

