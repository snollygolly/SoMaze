SoMaze
=========

SoMaze is a puzzle game created by [Evil Mouse Studios] for use with cryptocurrencies such as [Dogecoin].  It's kind of a mix between a maze, minesweeper, and Stratego.

Gameplay
----

There are basically two roles in SoMaze, that of the *Puzzle Creator* and that of the *Puzzle Solver*.

#####Puzzle Creator
* The Puzzle Creator first selects the size of the map they'd like to create.  The maximum size is currently 25x25
* After selecting the size for the map, the Puzzle Creator then places traps around the map.
* Some traps can be seen by the player (such as lava tiles), and some are hidden until activated by the player (such as mines).
* After designing the map and placing the traps, fees are set.
 * The Entrance Fee is the amount the Puzzle Solver pays the Puzzle Creator to attempt to solve the puzzle.  The Puzzle Creator gets this entire fee regard of if the Puzzle Solver wins or not.
 * The Reward is the amount the Puzzle Creator pays to the Puzzle Solver upon successful completion of the puzzle.  The Puzzle Creator will only pay this once.  Upon successful completion of the puzzle, the puzzle will be marked "deactivated" and will not be playable anymore.
 * The Creation Fee is an amount that is determined automatically based on the size of the map, the number of traps, and the type of traps.  Larger, more elaborate maps will have larger Creation Fees.  The Creation Fee is payed by the Puzzle Creator once when creating the map, and again every time a Puzzle Solver attempts to solve the puzzle.  It's a good idea to make sure your Entrance Fee is larger than the Creation Fee, or you'll consistently lose money.
* After all the fees are set, the Puzzle Creator must play through his puzzle before it goes live.  This is to ensure that the puzzle is solvable.

#####Puzzle Solver
* The Puzzle Solver selects the puzzle they'd like to attempt from a list of all currently active puzzles.  Puzzles are given a difficulty rating to assist the player in choosing the puzzle they'd like to play.
* Once the player has selected a puzzle, they will be required to pay the Entrance Fee before starting.
 * *Note: The creation fee is paid by the Puzzle Creator, NOT the Puzzle Solver.*
* The player will start at the entrance, and click one tile at a time until they reach the exit (or die).  Players are only able to move to adjecent tiles, and diagonal moves aren't allowed.
* If the player encounters a trap, they will be shown what trap they hit, and what the effects of the trap were.
* If the player dies before reaching the exit, no reward is given.
* If the player reaches the exit alive, the Reward is given, and the puzzle is marked "deactivated" (preventing future play on that puzzle)

Tips
-----------

* It's a good idea not to make your puzzle so difficult that it's almost impossible to beat.  This ends up costing you in the Creation Fee, and also discourages players from attempting it.
* The best puzzles are ones that are inexpensive to attempt, have few traps, but are surprisingly difficult.
* Keep in mind that even though a puzzle may SEEM impossible, the Creator has to beat it before they are allowed to submit it.



[Evil Mouse Studios]:http://evilmousestudios.com
[Dogecoin]:http://dogecoin.com/

    