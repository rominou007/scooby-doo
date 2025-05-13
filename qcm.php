
<!-- select * from questions order by rand() // to get random questions -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>QCM</h1>
        <div class="questions">
        <form action="result.php" method="post">
            <?php     
            include 'connect.php';        
                $sql="select * from questions order by rand() limit 10";
                $result=mysqli_query($id,$sql);
                //$_SESSION['data'] = mysqli_fetch_assoc($result);
                $i=1;
                while($row=mysqli_fetch_assoc($result)){
                    echo "<h3>".$i.". ".$row['libelleQ']."</h3>";
                    $sql2="select * from reponses where idq=".$row['idq']." order by rand()";
                    $result2=mysqli_query($id,$sql2);
                    while($row2=mysqli_fetch_assoc($result2)){
                        echo "<input type='radio' name='".$row['idq']."' value='".$row2['idr']."' checked>".$row2['libeller']."<br>";
                    }
                    $i++;
                }
            ?>
            <br><hr>
            <input type="submit" value="Valider">
        </div>
    </div>
</body>
</html>