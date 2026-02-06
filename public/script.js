const grid = document.getElementById("grid");
const score = document.getElementById("score");
const letters = "ABCDEFGHIJ";
let gameOverFlag = false;

const messageDiv = document.createElement('div');
messageDiv.id = 'message';
messageDiv.style.margin = '10px';
messageDiv.style.fontWeight = 'bold';
messageDiv.style.color = 'yellow';
grid.parentNode.insertBefore(messageDiv, grid);

async function resetGame() {
  await fetch("/battleship/server/reset.php");
}

resetGame();

function createGrid() {
  for (let r = 0; r < 10; r++) {
    for (let c = 0; c < 10; c++) {
      const cell = document.createElement("div");
      cell.classList.add("cell");

      const id = letters[r] + (c + 1);
      cell.dataset.id = id;

      cell.addEventListener("click", fire);
      grid.appendChild(cell);
    }
  }
}

async function fire(e) {
  const cell = e.target;
  const id = cell.dataset.id;

  if (gameOverFlag) {
    messageDiv.textContent = 'Game is over. Start a new game to play again.';
    return;
  }

  if (cell.classList.contains("hit") || cell.classList.contains("miss")) {
    return;
  }

  const res = await fetch("../server/fire.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ cell: id })
  });

  const data = await res.json();

  if (data.status === "hit") {
    cell.classList.add("hit");
  } else if (data.status === "miss") {
    cell.classList.add("miss");
  } else if (data.status === "duplicate") {
    messageDiv.textContent = 'You already fired at that cell.';
    // continue to update UI from the returned full state
  }

  if (data.sunk) {
    alert(`You sunk a ship of size ${data.sunk}!`);
  }

  // clear any previous messages for successful or duplicate updates
  if (data.status !== 'duplicate') messageDiv.textContent = '';

  // Safely convert values to numbers with fallbacks
  const safeScore = Number.isFinite(Number(data.score)) ? Number(data.score) : 0;
  const safeHits = Number.isFinite(Number(data.hits)) ? Number(data.hits) : 0;
  const safeMisses = Number.isFinite(Number(data.misses)) ? Number(data.misses) : 0;
  const safeTurns = Number.isFinite(Number(data.turns)) ? Number(data.turns) : 0;
  const safeMax = Number.isFinite(Number(data.maxTurns)) ? Number(data.maxTurns) : 0;

  score.textContent = `Score: ${safeScore} | Hits: ${safeHits} | Misses: ${safeMisses} | Turns: ${safeTurns}/${safeMax}`;

  if (data.gameOver) {
    gameOverFlag = true;
    grid.classList.add('game-over');
    if (data.win) {
      alert(`You win! Final score: ${data.score} (bonus for ${data.remainingMoves} remaining moves)`);
    } else if (data.lose) {
      alert(`Game Over! Final score: ${data.score}`);
    }
  }
}

createGrid();