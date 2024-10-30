/**
 * @typedef {{x: Number, y: Number}} Point
 * @typedef {{x: Number, y: Number, color: Number}} Move
 * @typedef {{rule: String, points: Point[]}} WinningSequence
 */

export class Game {
    /**
     * @param {{gameId: String, moves: Move[], winningSequences: WinningSequence[]}} game
     */
    constructor(game) {
        this.gameId = game.gameId;
        this.moves = game.moves;
        this.winningSequences = game.winningSequences;
        this.onMoveAppendedObservers = [];
    }

    /**
     * Returns the number of moves.
     *
     * @returns {Number}
     */
    numberOfMoves() {
        return this.moves.length;
    }

    /**
     * Append a move. If it's already there, it'll silently not appended.
     *
     * @param {Move} move
     */
    appendMove(move) {
        if (!this.hasMove(move)) {
            this.moves.push(move);

            this.onMoveAppendedObservers.forEach(callback => callback(move));
        }
    }

    /**
     * Check if the game has the given move.
     *
     * @param {Move} move
     *
     * @returns {boolean}
     */
    hasMove(move) {
        // this.moves.indexOf(move) doesn't work due === check.
        return JSON.stringify(this.moves).indexOf(JSON.stringify(move)) !== -1;
    }

    /**
     * Register observer which gets notified if a new move was appended.
     *
     * @param {Function} callback
     */
    onMoveAppended(callback) {
        this.onMoveAppendedObservers.push(callback);
    }
}
