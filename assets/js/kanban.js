function initKanban(boards, kanbanData) {

  //console.log('Received Kanban Data:', kanbanData); // Debugging line

  let processedBoards = boards.map(board => {
      return {
          id: board.name,  // Using the 'name' attribute as ID
          title: board.name,
          item: []
      };
  });

  kanbanData.forEach(lead => {
      let board = processedBoards.find(b => b.title === lead.lead_status);
      if (board) {
          console.log(`Adding lead to board: ${board.title}. Item ID: ${lead.id}`);
          board.item.push({
              id: lead.id.toString(),
              title: createKanbanCard(lead)
          });
      } else {
          console.error(`No board found for lead_status: ${lead.lead_status}. Item ID: ${lead.id}`);
      }
  });

  const KanbanTest = new jKanban({
      element: "#myKanban",
      gutter: "10px",
      widthBoard: "280px",
      itemHandleOptions: {
          enabled: false,
      },
      boards: processedBoards,
      click: function(el) {
          console.log("Trigger on all items click!");
          var leadId = el.getAttribute('data-eid');
          console.log('Lead ID:', leadId);
          openLeadCard(leadId);
      },
      context: function(el, e) {
          console.log("Trigger on all items right-click!");
      },
      dropEl: function(el, target, source, sibling) {
          console.log(target.parentElement.getAttribute('data-id'));
          var boardId = target.parentElement.getAttribute('data-id');
          var leadId = el.getAttribute('data-eid');
          console.log('Lead ID:', leadId);
          updateStatus(leadId, boardId);
          //console.log(el, target, source, sibling);
      },
      buttonClick: function(el, boardId) {
          var formItem = document.createElement("form");
          formItem.setAttribute("class", "itemform");
          formItem.innerHTML =
              '<div class="form-group"><textarea class="form-control" rows="2" autofocus></textarea></div><div class="form-group"><button type="submit" class="btn btn-primary btn-xs pull-right">Submit</button><button type="button" id="CancelBtn" class="btn btn-default btn-xs pull-right">Cancel</button></div>';

          KanbanTest.addForm(boardId, formItem);
          formItem.addEventListener("submit", function(e) {
              e.preventDefault();
              var text = e.target[0].value;
              KanbanTest.addElement(boardId, {
                  title: text
              });
              formItem.parentNode.removeChild(formItem);
          });
          document.getElementById("CancelBtn").onclick = function() {
              formItem.parentNode.removeChild(formItem);
          };
      }
  });
}