<?php
session_start();

if (!isset($_SESSION['uname'])) {

    header("Location:logout.php");
    exit();
}

if ($_SESSION['type'] == "viewer") {

    echo "
        <script>
            alert('You do not have privilege to access the page. Please contact website manager.');
            window.location.href='Summarizer.php';
        </script>";
}

//TO DO"  if user is Admin, Maintainer or developer show the buttons
//if user = viewer hide the buttons -- this functionality should be in loader.php page
//export error messages during uploading or uploading

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
        <title>Loader</title>
        
    </head>
    <body>
        <form name="form" action="tableTools.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="file" accept=".csv">   
            <input type ="submit" name="flexible_update" value="Flexible Update"> 
            <input type="submit" name="append" value="Append">  
           
            <input type="submit" name="full_update" value="Full Update">
        </form> 

           
            <form name="form" action="Download.php" method="POST" enctype="multipart/form-data">
             <input type="submit" name="reg_download" style="float:right" value="Regular Download">  <!-- added new button for regular download -->
              </form>
       

            <form name="form" action="restore.php" method="POST" enctype="multipart/form-data">
                        <input type="submit" name="submit" style="float:right" value="Restore from file">
             </form>
        
			<form name="form" action="Download.php" method="POST" enctype="multipart/form-data">
                <input type="submit" name="full_download" style="float:right" value="Full Download">
            </form> 

			<button><a href="Summarizer.php">Main Page</a></button>
         
                <?php
                        if ($_SESSION['type'] == "Admin" || $_SESSION["type"] == "Programmer") {
                            echo '
    <div id="mytable">
        <h1>Backup Table </h1>
        <table class="data-table" id="mytable">
            <caption class="title">Backup table Data (only available to programmers and administrators)</caption>
            <thead>
                <tr>
                    <th>Id</th>
                    <th>User</th>
                    <th>Filename</th>
                    <th>DATE</th>
                    <th>Locked</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
        ';
                        }
                        ?>
                        <?php
                        // create and load backup_table
                        $connect = mysqli_connect("rendertech.com", $_SESSION['uname_long'], $_SESSION['psw'], "pupone_Summarizer");
                        if (!$connect) {
                            die('Could not connect: ' . mysqli_error());
                        }
                        // added order by DESC to reverse order of results
                        // by Tom Tran - 2018-05-30
                        $query = "SELECT * FROM backup_table ORDER BY id DESC;";
                        if ($result = mysqli_query($connect, $query)) {
                            while ($row = mysqli_fetch_array($result)) {
                                if ($row['locked'] == 1) {
                                    $row['locked'] = "YES";
                                } else {
                                    $row['locked'] = "NO";
                                }
                                if ($_SESSION["type"] == "Admin") {
                                    echo '<tr>
                        <td>' . $row['id'] . '</td>
                        <td>' . $row['user'] . '</td>
                        <td id=' . 'deleteTB' . $row['id'] . '>' . $row['filename'] . '</td>
                        <td>' . $row['date'] . '</td>
                        <td id=' . $row['id'] . '>' . $row['locked'] . '</td>
                        <td><input id=' . 'btn' . $row['id'] . ' class="lockRow" data-id=' . $row['id'] . ' type="button" name="edit"><input class="delete" data-id=' . $row['id'] . ' type="button" name="edit" value="Delete"><a href="tableTools.php?filename=' . $row['filename'] . '"><input class="downloadTable" data-id=' . $row['filename'] . ' type="button" name="edit" value="Download"></a></td>
                    </tr>
                    <script>
                        if($("#' . $row['id'] . '").text()=="YES"){
                            $("#' . 'btn' . $row['id'] . '").val("Unlock")
                            $("#' . 'btn' . $row['id'] . '").removeClass("lockRow").addClass("unlockRow")
                        }else{
                            $("#' . 'btn' . $row['id'] . '").val("Lock")
                            $("#' . 'btn' . $row['id'] . '").removeClass("unlockRow").addClass("lockRow")
                        }
                    </script>
                ';
                                } else if ($_SESSION["type"] == "Programmer") {
                                    echo '<tr>
                    <td>' . $row['id'] . '</td>
                    <td>' . $row['user'] . '</td>
                    <td id=' . 'deleteTB' . $row['id'] . '>' . $row['filename'] . '</td>
                    <td>' . $row['date'] . '</td>
                    <td id=' . $row['id'] . '>' . $row['locked'] . '</td>
                    <td><input class="delete" data-id=' . $row['id'] . ' type="button" name="edit" value="Delete"><a href="tableTools.php?filename=' . $row['filename'] . '"><input class="downloadTable" data-id=' . $row['filename'] . ' type="button" name="edit" value="Download"></a></td>
                </tr>
            ';
                                }
                            }
                        }
                        ?>
                        </tbody>
                        </table>
                        </div>
                        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> 
                        <?php
                        //lock the selected row in backup_table 
                        if (isset($_POST["id"])) {
                            $id = $_POST["id"];
                            $editSQL = "UPDATE backup_table SET locked=1 WHERE id=$id";
                            mysqli_query($connect, $editSQL);
                        }
                        //unlock the selected row in backup_table
                        if (isset($_POST["unlockId"])) {
                            $unlockId = $_POST["unlockId"];
                            $editSQL = "UPDATE backup_table SET locked=0 WHERE id=$unlockId";
                            mysqli_query($connect, $editSQL);
                        }
                        //delete the selected row from backup_table and the table in database
                        if ($_SESSION["type"] == "Programmer") {
                            if (isset($_POST["deleteId"])) {
                                $unlockId = $_POST["deleteId"];
                                $newID = $_POST["newID"];
                                $checkStatus = "SELECT * FROM backup_table WHERE id=$newID limit 1";
                                if ($result = mysqli_query($connect, $checkStatus)) {
                                    while ($row = mysqli_fetch_array($result)) {
                                        if ($row["locked"] == 1) {
                                            
                                        }
                                        //if it is unlocked, drop the table
                                        if ($row["locked"] == 0) {
                                            $updateSQL = "DELETE FROM backup_table WHERE id=$newID";
                                            $editSQL = "DROP TABLE `$unlockId`";
                                            mysqli_query($connect, $editSQL);
                                            mysqli_query($connect, $updateSQL);
                                        }
                                    }
                                }
                            };
                        }
                        if ($_SESSION["type"] == "Admin") {
                            if (isset($_POST["deleteId"])) {
                                $unlockId = $_POST["deleteId"];
                                $newID = $_POST["newID"];

                                $checkStatus = "SELECT * FROM backup_table WHERE id=$newID limit 1";

                                $updateSQL = "DELETE FROM backup_table WHERE id=$newID";
                                $editSQL = "DROP TABLE `$unlockId`";
                                mysqli_query($connect, $editSQL);
                                mysqli_query($connect, $updateSQL);
                            }
                        };

                        //send post request for "lock", "unlock" and "delete" button
                        echo '
    <script>
        $(document).on("click",".lockRow",function(){
            let id=$(this).attr("data-id")
            console.log(id)
            $(this).val("Unlock")
            $(this).removeClass("lockRow").addClass("unlockRow")
        $.ajax({
            url:"loader.php",
            type:"POST",
            data:{"id":id}
        }).then(function(data){
                $(`#${id}`).text("YES")
               
        })
            
        })
        $(document).on("click",".unlockRow",function(){
            let unlockId=$(this).attr("data-id")
            $(this).val("Lock")
            $(this).removeClass("unlockRow").addClass("lockRow")
        $.ajax({
            url:"loader.php",
            type:"POST",
            data:{"unlockId":unlockId}
        }).then(function(data){
                $(`#${unlockId}`).text("NO")
        })
            
        })
    </script>
    ';
                        if ($_SESSION['type'] == "Programmer") {
                            echo '
        <script>
            $(document).on("click",".delete",function(){
                let id=$(this).attr("data-id");
                let deleteId="deleteTB"+$(this).attr("data-id")
                var fileName=$(`#${deleteId}`).text().trim()
                console.log(fileName)
            $.ajax({
                url:"loader.php",
                type:"POST",
                data:{"deleteId":fileName,
                      "newID":id}
            }).then(function(data){
                    if($(`#${id}`).text().trim()=="YES"){
                        alert("The table is locked");
                    }else{
                        window.location.reload();
                    }

            })
                
            })
        </script>
        ';
                        };
                        if ($_SESSION['type'] == "Admin") {
                            echo '
        <script>
            $(document).on("click",".delete",function(){
                let id=$(this).attr("data-id");
                let deleteId="deleteTB"+$(this).attr("data-id")
                var fileName=$(`#${deleteId}`).text().trim()
                console.log(fileName)
            $.ajax({
                url:"loader.php",
                type:"POST",
                data:{"deleteId":fileName,
                        "newID":id}
            }).then(function(data){
                window.location.reload();
            })
                
            })
        </script>
        ';
                        };
                        ?>

                        </body>
                        <footer>
                            <center><p>working prototype 1.1.2e</p></center>
                            <center><p>Date Released: 2018-07-11</p></center>
                        </footer>
                        </html>
