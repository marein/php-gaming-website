export class Game {
    /**
     * @param {String} gameId
     * @param {{x:Number, y:Number, color:Number}[]} moves
     * @param {{x:Number, y:Number}[]} moves
     */
    constructor(gameId, moves, winningSequence) {
        this.gameId = gameId;
        this.moves = moves;
        this.winningSequence = winningSequence;
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
     * @param {{x:Number, y:Number, color:Number}} move
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
     * @param {{x:Number, y:Number, color:Number}} move
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

    /**
     * Create the game from a raw object.
     *
     * @param {{gameId:String, moves:{x:Number, y:Number, color:Number}[], winningSequence:{x:Number, y:Number}[]}} object
     */
    static fromObject(object) {
        return new this(
            object.gameId,
            object.moves,
            object.winningSequence
        );
    }
}
