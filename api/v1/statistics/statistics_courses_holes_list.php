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
  </style>
  </head>
  <body>

  <div style="position: fixed; top: 20px; left: 20px;">
    <a href="javascript:history.back()" class="back-button" style="background-color: #F9CF58; color: #333; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-family: 'Poppins'; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
      Go Back
    </a>
  </div>

    <div class="bg-white text-zinc-900 dark:bg-zinc-800 dark:text-white" style="margin-top:60px">
        <div class="divide-y divide-zinc-300 dark:divide-zinc-700 holes_list">

        </div>
    </div>


  <script>
    $(document).ready(function() {
      // Assuming course_id and token are available in the URL query parameters
      const urlParams = new URLSearchParams(window.location.search);
      const course_id = urlParams.get('course_id');
      const token = urlParams.get('token');

      if (course_id && token) {
        $.ajax({
          url: 'https://duendedisplay.co.za/gimme/api/v1/statistics/courses_holes_and_pars.php',
          type: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            course_id: course_id,
            token: token
          }),
          dataType: 'json',
          success: function(response) {
            const holes_list = $('.holes_list');
            response.course_holes_and_pars.forEach(hole => {
                const holeElement = `
                    <div class="p-4 flex justify-between items-center border_bottom">
                        <span class="titles">${hole.hole_name}</span>
                        <span class="chevrons"><span class="description">Par ${hole.par}</span></span>
                    </div>
                `;
                holes_list.append(holeElement);
            });
            // Handle the response data here, e.g., display the holes and pars on the page
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error:', textStatus, errorThrown);
          }
        });
      } else {
        console.error('Missing course_id or token in the URL parameters.');
      }
    });
  </script>
  </body>
</html>