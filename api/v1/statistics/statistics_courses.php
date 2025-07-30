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


<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  
  
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

    a{
      text-decoration: none !important;
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
        <a href="#" data-mode="event" class="tab-item inline-block p-4 rounded-t-lg hover:text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 dark:hover:text-gray-300">Events Mode</a>
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
          } else if (mode === 'event') {
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
        <!-- Content for match mode -->
        <div class="divide-y match_dynamic_courses mb-4">
<!-- 
            <div class="p-4 flex justify-between items-center card m-4">
              <div class="flex justify-between w-full">
                <div class="flex justify-between">
                  <img src="https://duendedisplay.co.za/gimme/api/v1/assets/img/Group_7.png" alt="Benoni Lakeview" class="mr-4">
                  <div>
                    <span class="titles">Benoni Lakeview</span><br/>
                    <span class="description">Johannesburg</span>
                  </div>
                </div>
                <span class="chevrons">&gt;</span>
              </div>
            </div> -->

        </div>

    </div>

    <div class="bg-white text-zinc-900 dark:bg-zinc-800 dark:text-white event_mode" style="display: none;">
        <!-- Content for event mode -->
        <div class="divide-y event_dynamic_courses">

            <!-- <div class="p-4 flex justify-between items-center card m-4">
              <div class="flex justify-between w-full">
                <div class="flex justify-between">
                  <img src="https://duendedisplay.co.za/gimme/api/v1/assets/img/Group_7.png" alt="Benoni Lakeview" class="mr-4">
                  <div>
                    <span class="titles">Benoni Lakeview</span><br/>
                    <span class="description">Johannesburg</span>
                  </div>
                </div>
                <span class="chevrons">&gt;</span>
              </div>
            </div> -->


        </div>

    </div>


    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const token = "<?php echo $_GET['token']?>";
        const tabs = document.querySelectorAll('.tab-item');
        let currentGamemode = 'match'; // Default gamemode


          // Create and display the full page loader
          var loader = document.createElement('div');
          loader.id = 'fullPageLoader';
          loader.style.position = 'fixed';
          loader.style.top = '0';
          loader.style.left = '0';
          loader.style.width = '100%';
          loader.style.height = '100%';
          loader.style.backgroundColor = 'rgba(255, 255, 255, 1)';
          loader.style.display = 'flex';
          loader.style.justifyContent = 'center';
          loader.style.alignItems = 'center';
          loader.style.zIndex = '9999';// Enhanced bounce animation for more bounce effect
          document.body.appendChild(loader);

          var logo = document.createElement('img');
          logo.src = 'https://duendedisplay.co.za/gimme/api/v1/assets/img/logo.png';
          logo.style.animation = 'bounce 2s infinite';
          logo.style.height = '100px';
          loader.appendChild(logo);
          document.body.appendChild(loader);

          // Add CSS for bounce animation
          var style = document.createElement('style');
          style.innerHTML = `
            @keyframes bounce {
              0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
              }
              40% {
                transform: translateY(-30px);
              }
              60% {
                transform: translateY(-15px);
              }
            }
          `;
          document.head.appendChild(style);

        tabs.forEach(tab => {
          tab.addEventListener('click', function(event) {
            event.preventDefault();
            currentGamemode = this.getAttribute('data-mode');
          });
        });

        function fetchDataForGamemode(gamemode) {
          const token = "<?php echo $_GET['token']?>";
          $.ajax({
            url: 'https://duendedisplay.co.za/gimme/api/v1/statistics/courses_played_on.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
              token: token,
              gamemode: gamemode
            }),
            dataType: 'json',
            success: function(data) {
              console.log(data);
              if (gamemode === 'match') {
                const matchContainer = document.querySelector('.match_dynamic_courses');
                matchContainer.innerHTML = ''; // Clear the container before adding new content

                if (data.courses_info.length === 0) {
                  setTimeout(function() {
                    document.body.removeChild(loader);
                  }, 800); // Set the duration of the loader to 3 seconds
                  const emptyContainer = document.createElement('div');
                  emptyContainer.className = 'p-4 flex flex-col justify-center items-center';
                  emptyContainer.style.height = '200px'; // Set a fixed height for the empty container

                  const logo = document.createElement('img');
                  logo.src = 'https://duendedisplay.co.za/gimme/api/v1/assets/img/logo.png';
                  logo.style.width = '100px'; // Set logo size
                  logo.style.marginBottom = '20px'; // Space between logo and text
                  logo.style.fontWeight = 'normal'; // Space between logo and text

                  const emptyText = document.createElement('span');
                  emptyText.textContent = 'No items to display';

                  emptyContainer.appendChild(logo);
                  emptyContainer.appendChild(emptyText);
                  matchContainer.appendChild(emptyContainer);
                  
                }else{                
                  data.courses_info.forEach(course => {
                    const courseElement = document.createElement('a');
                    courseElement.className = 'p-4 flex justify-between items-center card m-4';
                    courseElement.href = `statistics_courses_holes_list.php?course_id=${course.id}&token=${token}`; // Assuming 'next_page.php' and 'course_id' as the URL and parameter
                    courseElement.innerHTML = `
                      <div class="flex justify-between w-full">
                        <div class="flex justify-between">
                          <img src="https://duendedisplay.co.za/gimme/api/v1/assets/img/Group_7.png" alt="${course.course_name}" class="mr-4">
                          <div>
                            <span class="titles">${course.course_name}</span><br/>
                            <span class="description">${course.course_address}</span>
                          </div>
                        </div>
                        <span class="chevrons">&gt;</span>
                      </div>
                    `;
                    matchContainer.appendChild(courseElement);

                    setTimeout(function() {
                      document.body.removeChild(loader);
                    }, 800);

                  });
                }
              } else if (gamemode === 'event') {
                const eventContainer = document.querySelector('.event_dynamic_courses');
                eventContainer.innerHTML = ''; // Clear the container before adding new content

                if (data.courses_info.length === 0) {
                  setTimeout(function() {
                    document.body.removeChild(loader);
                  }, 800); // Set the duration of the loader to 3 seconds
                  const emptyContainer = document.createElement('div');
                  emptyContainer.className = 'p-4 flex flex-col justify-center items-center';
                  emptyContainer.style.height = '200px'; // Set a fixed height for the empty container

                  const logo = document.createElement('img');
                  logo.src = 'https://duendedisplay.co.za/gimme/api/v1/assets/img/logo.png';
                  logo.style.width = '100px'; // Set logo size
                  logo.style.marginBottom = '20px'; // Space between logo and text
                  logo.style.fontWeight = 'normal'; // Space between logo and text

                  const emptyText = document.createElement('span');
                  emptyText.textContent = 'No items to display';

                  emptyContainer.appendChild(logo);
                  emptyContainer.appendChild(emptyText);
                  matchContainer.appendChild(emptyContainer);
                  
                }else{   
                  data.courses_info.forEach(course => {
                    const courseElement = document.createElement('a');
                    courseElement.className = 'p-4 flex justify-between items-center card m-4';
                    courseElement.href = `statistics_courses_holes_list.php?course_id=${course.id}&token=${token}`; // Assuming 'next_page.php' and 'course_id' as the URL and parameter
                    courseElement.innerHTML = `
                      <div class="flex justify-between w-full">
                        <div class="flex justify-between">
                          <img src="https://duendedisplay.co.za/gimme/api/v1/assets/img/Group_7.png" alt="${course.course_name}" class="mr-4">
                          <div>
                            <span class="titles">${course.course_name}</span><br/>
                            <span class="description">${course.course_address}</span>
                          </div>
                        </div>
                        <span class="chevrons">&gt;</span>
                      </div>
                    `;
                    eventContainer.appendChild(courseElement);

                    setTimeout(function() {
                      document.body.removeChild(loader);
                    }, 800);

                  });
                }
              }
              // console.log('Data fetched successfully for gamemode:', gamemode);
              // console.log(data);
              // Handle data here
            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.error('Error fetching data for gamemode:', gamemode, textStatus, errorThrown);

              setTimeout(function() {
                    document.body.removeChild(loader);
                  }, 800);

            }
          });
        }

        // Initial fetch for default gamemode
        fetchDataForGamemode(currentGamemode);

        // Fetch data on tab change
        tabs.forEach(tab => {
          tab.addEventListener('click', function() {
            fetchDataForGamemode(this.getAttribute('data-mode'));
          });
        });
      });
    </script>


    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
  </body>
</html>