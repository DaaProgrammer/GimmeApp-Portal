<?php
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://codegen.plasmic.app/api/v1/loader/html/preview/esTjRhLtsE3EPjimsSD4bZ/Login?hydrate=1&embedHydrate=1");
// Provide the project ID and public API token.
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
  "x-plasmic-api-project-tokens: esTjRhLtsE3EPjimsSD4bZ:6jOWRzCmARDDE2g4WT8SaRI44AZMB6u1xBK93OXAa84kmHCAJJBzM6t4XMphvxFN1RcFGeMuNomMV6DtRDBRw"
));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($curl);
curl_close($curl);

$result = json_decode($response);
echo $result->html;
?>