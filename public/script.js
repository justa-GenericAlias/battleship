const grid = document.getElementById("grid");
const score = document.getElementById("score");
const letters = "ABCDEFGHIJ";

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
  }

  score.textContent =
  `Hits: ${data.hits} | Misses: ${data.misses} | Turns: ${data.turns}/${data.maxTurns}`;

  if (data.sunkShipMessage) {
    messageDiv.textContent = data.sunkShipMessage;
  } else {
    messageDiv.textContent = '';
  }

  if (data.gameOver) {
    if (data.win) {
      alert("You win! You sank all the ships.");
    } else if (data.lose) {
      alert("Game Over! You ran out of turns.");
    }
  }
}

createGrid();