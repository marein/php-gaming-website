/**
 * @typedef {{x: Number, y: Number}} Point
 * @typedef {{x: Number, y: Number, color: Number}} Move
 * @typedef {{rule: String, points: Point[]}} WinningSequence
 */

export class Game {
    /**
     * @param {{
     *   gameId: String,
     *   redPlayerId: String,
     *   yellowPlayerId: String,
     *   currentPlayerId: String,
     *   moves: Move[],
     *   winningSequences: WinningSequence[]
     * }} game
     */
    constructor(game) {
        this.gameId = game.gameId;
        this.redPlayerId = game.redPlayerId;
        this.yellowPlayerId = game.yellowPlayerId;
        this.currentPlayerId = game.currentPlayerId;
        this.moves = game.moves;
        this.winningSequences = game.winningSequences;
    }

    /**
     * @returns {Number}
     */
    numberOfMoves() {
        return this.moves.length;
    }

    /**
     * @param {Move} move
     */
    appendMove(move) {
        if (this.hasMove(move)) return;

        this.moves.push(move);
    }

    /**
     * @param {Move} move
     */
    removeMove(move) {
        this.moves = this.moves.filter(m => m.x !== move.x || m.y !== move.y);
    }

    /**
     * @param {Move} move
     */
    hasMove(move) {
        return this.moves.find(m => m.x === move.x && m.y === move.y) !== undefined;
    }
}
