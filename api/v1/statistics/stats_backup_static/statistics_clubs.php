<html>
  <head>
    <title></title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .chevrons{
      float:right;
      font-weight:bold;
      font-size:17px;
    }
    .border_bottom{
      border-bottom:2px solid #f1f1f1;
    }

    .description{
      color:#aaa;
      font-size:14px;
      font-family:"Poppins";
    }

    .titles{
      font-weight:bold;
      font-size:17px;
      font-family:"Poppins";
    }
  </style>
  </head>
  <body>
  <div style="position: fixed; top: 20px; left: 20px;">
    <a href="javascript:history.back()" class="back-button" style="background-color: #F9CF58; color: #333; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-family: 'Poppins'; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
      Go Back
    </a>
  </div>
  <ul id="tabMenu" class="flex flex-wrap text-sm font-medium text-center text-gray-500 border-b border-gray-200 dark:border-gray-700 dark:text-gray-400" style="margin-top:60px">
    <li class="me-2">
        <a href="#" data-mode="match" class="tab-item inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300">Match Mode</a>
    </li>
    <li class="me-2">
        <a href="#" data-mode="events" class="tab-item inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300">Events Mode</a>
    </li>
  </ul>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const tabs = document.querySelectorAll('.tab-item');
      const matchModeDiv = document.querySelector('.match_mode');
      const eventModeDiv = document.querySelector('.event_mode');
      matchModeDiv.style.display = 'block';
      eventModeDiv.style.display = 'none';

      tabs.forEach(tab => {
        tab.addEventListener('click', function(event) {
          event.preventDefault();
          const mode = this.getAttribute('data-mode');
          
          if (mode === 'match') {
            matchModeDiv.style.display = 'block';
            eventModeDiv.style.display = 'none';
          } else if (mode === 'events') {
            matchModeDiv.style.display = 'none';
            eventModeDiv.style.display = 'block';
          }
          
          tabs.forEach(t => t.classList.remove('text-blue-600', 'bg-gray-100', 'active', 'dark:bg-gray-800', 'dark:text-blue-500'));
          this.classList.add('text-blue-600', 'bg-gray-100', 'active', 'dark:bg-gray-800', 'dark:text-blue-500');
        });
      });
    });
  </script>

    <div class="bg-white text-zinc-900 dark:bg-zinc-800 dark:text-white match_mode">
        <div class="divide-y divide-zinc-300 dark:divide-zinc-700 headings">
          <div class="p-4 flex justify-between items-center border_bottom">
              <span class="titles"><strong>Overall Clubs Played</strong></span>
              <span class="titles"><strong>Overall</strong></span>
          </div>
        </div>

        <!-- Content for match mode -->
            <div class="p-4 flex justify-between items-center border_bottom">
                <span class="titles">3 Wood</span>
                <div class="chevrons d-flex align-items-center">
                  <span class="description mr-2">N/A</span>
                  <span>&gt;</span>
                </div>
            </div>

            <div class="divide-y divide-zinc-300 dark:divide-zinc-700">
            <div class="p-4 flex justify-between items-center border_bottom">
                <span class="titles">Driver</span>
                <div class="chevrons d-flex align-items-center">
                  <span class="description mr-2">N/A</span>
                  <span>&gt;</span>
                </div>
            </div>

            <div class="p-4 flex justify-between items-center border_bottom">
                <span class="titles">Tees</span>
                <div class="chevrons d-flex align-items-center">
                  <span class="description mr-2">N/A</span>
                  <span>&gt;</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white text-zinc-900 dark:bg-zinc-800 dark:text-white event_mode" style="display: none;">
        <div class="divide-y divide-zinc-300 dark:divide-zinc-700 headings">
          <div class="p-4 flex justify-between items-center border_bottom">
              <span class="titles"><strong>Overall Clubs Played</strong></span>
              <span class="titles"><strong>Overall</strong></span>
          </div>
        </div>
    
    <!-- Content for event mode -->
        <div class="divide-y divide-zinc-300 dark:divide-zinc-700">
            <div class="p-4 flex justify-between items-center border_bottom">
                <span class="titles">Driver</span>
                <div class="chevrons d-flex align-items-center">
                  <span class="description mr-2">N/A</span>
                  <span>&gt;</span>
                </div>
            </div>
            <div class="p-4 flex justify-between items-center border_bottom">
                <span class="titles">Tees</span>
                <div class="chevrons d-flex align-items-center">
                  <span class="description mr-2">N/A</span>
                  <span>&gt;</span>
                </div>
            </div>
            <div class="p-4 flex justify-between items-center border_bottom">
                <span class="titles">3 Wood</span>
                <div class="chevrons d-flex align-items-center">
                  <span class="description mr-2">N/A</span>
                  <span>&gt;</span>
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
  </body>
</html>