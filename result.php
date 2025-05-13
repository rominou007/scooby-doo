<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Resultat</title>
</head>
<body>
<div class="container">
    <h1>QCM</h1>
    <?php
        $id = mysqli_connect("localhost", "root", "", "qcm");
        $i = 1;
        foreach ($_POST as $question_name => $selected_value) {
            $sql = "SELECT * FROM questions WHERE idq = " . $question_name;
            $result = mysqli_query($id, $sql);
            $row = mysqli_fetch_assoc($result);
            echo "<h3>" . $i . ". " . $row['libelleQ'] . "</h3>";

            $sql2 = "SELECT * FROM reponses WHERE idr = " . $selected_value;
            $result2 = mysqli_query($id, $sql2);
            $row2 = mysqli_fetch_assoc($result2);
            echo "<p>Votre réponse : " . $row2['libeller'] . "</p>";

            // Vérifier si la réponse est correcte
            if ($row2['verite'] == 1) {
                echo "<p>✅ Correcte</p>"; // Emoji pour correcte
            } else {
                echo "<p>❌ Fausse</p>"; // Emoji pour fausse

                // Afficher la bonne réponse
                $sql3 = "SELECT * FROM reponses WHERE idq = " . $question_name . " AND verite = 1";
                $result3 = mysqli_query($id, $sql3);
                $row3 = mysqli_fetch_assoc($result3);
                echo "<p>La bonne réponse était : " . $row3['libeller'] . "</p>";
            }
            $i++;
        }
    ?>
</div>
</body>
</html>