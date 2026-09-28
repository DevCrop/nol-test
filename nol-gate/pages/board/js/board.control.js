import { BoardController } from "../../../resource/js/Controller/BoardController.js";

document.addEventListener("DOMContentLoaded", () => {
  const page = document.body.dataset.page;

  if (!page) return;
  switch (page) {
    case "board":
      const boardController = new BoardController();
      boardController.init();
      break;

    default:
      break;
  }
});
