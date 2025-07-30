<html>
  <head>
    <title></title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
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
  </style>
  </head>
  <body>
    <div class="bg-white text-zinc-900 dark:bg-zinc-800 dark:text-white">
        <div class="divide-y divide-zinc-300 dark:divide-zinc-700">
<!--             <div class="p-4 flex justify-between items-center border_bottom">
                <span class="titles">Overview</span><br/>
                <span class="description">Rounds played, Total score,...</span>
                <span class="chevrons">&gt;</span>
            </div> -->
            <?php
                  $path = "statistics_clubs.php?token=".$_GET['token'];
            ?>
            <div>
              <a href="<?php echo $path;?>" style="text-decoration: none;color:#212529">   
                <div class="p-4 flex justify-between items-center border_bottom">
                    <span class="titles">Clubs</span><br/>
                    <span class="description">Woods, Hybrids, Irons, Wedges...</span>
                    <span class="chevrons">&gt;</span>
                </div>          
              </a>
            </div>
            <div>
              <?php
                  $path = "statistics_courses.php?token=".$_GET['token'];
              ?>
              <a href="<?php echo $path;?>" style="text-decoration: none;color:#212529">
                <div class="p-4 flex justify-between items-center border_bottom">
                    <span class="titles">Courses</span><br/>
                    <span class="description">Course specific statistics</span>
                    <span class="chevrons">&gt;</span>
                </div>
              </a>
            </div>
            <div>
              <?php
                  $path = "statistics_overall_scoring.php?token=".$_GET['token'];
              ?>
              <a href="<?php echo $path;?>" style="text-decoration: none;color:#212529">
                <div class="p-4 flex justify-between items-center border_bottom">
                    <span class="titles">Overall Scoring</span><br/>
                    <span class="description">Eagles, Birdies, Pars, Bogeys...</span>
                    <span class="chevrons">&gt;</span>
                </div>
              </a>
            </div>
            <div class="dynamic_pars">
               <a href="#" style="text-decoration: none;color:#212529">
                  <div class="p-4 flex justify-between items-center border_bottom">
                      <span class="titles">Par 3 Scoring</span><br/>
                      <span class="description">Eagles, Birdies, Pars, Bogeys...</span>
                      <span class="chevrons">&gt;</span>
                  </div>
                </a>
            </div>

        </div>
    </div>


    <script>
       var token = "<?php echo $_GET['token']?>";
        document.addEventListener('DOMContentLoaded', function() {
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

          $.ajax({
            url: 'unique_pars.php', // URL to the API endpoint that returns the JSON
            type: 'POST',
            contentType: 'application/json', // Set content type to JSON
            data: JSON.stringify({ token: token }), // Data payload in JSON format
            dataType: 'json', // Specify the expected data type of the response
            success: function(data) {
              var container = document.querySelector('.dynamic_pars');
              container.innerHTML = ''; // Clear the container before adding new content
              data.unique_pars.forEach(function(par) {
                var link = document.createElement('a');
                link.href = 'statistics_par_scoring.php?par='+ par.par + '&token=' + token; // Placeholder href
                link.className = 'p-4 flex justify-between items-center';
                link.style.textDecoration = 'none'; // Remove default anchor styling
                link.style.color = '#212529'; // Set text color to match design

                var div = document.createElement('div');
                div.className = 'p-4 flex justify-between items-center border_bottom';
                div.innerHTML = '<span class="titles">' + par.par_label + '</span><br/>' +
                                '<span class="description">Eagles, Birdies, Pars, Bogeys...</span>' +
                                '<span class="chevrons">&gt;</span>';

                link.appendChild(div);
                container.appendChild(link);
              });
              // Remove the loader after successful data fetch
        
              setTimeout(function() {
                document.body.removeChild(loader);
              }, 800); // Set the duration of the loader to 3 seconds


            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.error('Error fetching scoring data: ' + textStatus + ', ' + errorThrown);
              // Remove the loader if there is an error
              setTimeout(function() {
                document.body.removeChild(loader);
              }, 800); // Set the duration of the loader to 3 seconds

            }
          });
        });
      </script>

  </body>
</html>