<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

/* ---------------- ADD POST ---------------- */
if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];

    mysqli_query($conn, "INSERT INTO posts(title,content) VALUES('$title','$content')");
}

/* ---------------- SEARCH ---------------- */
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

/* ---------------- PAGINATION ---------------- */
$limit = 5; // posts per page
$page = 1;

if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
    if ($page < 1) $page = 1;
}

$start = ($page - 1) * $limit;

/* Count total posts (for pagination) */
$countQuery = "SELECT COUNT(*) AS total FROM posts";

if ($search != "") {
    $countQuery .= " WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
}

$countResult = mysqli_query($conn, $countQuery);
$countRow = mysqli_fetch_assoc($countResult);
$totalPosts = $countRow['total'];

$totalPages = ceil($totalPosts / $limit);

/* Fetch posts with search + pagination */
$query = "SELECT * FROM posts";

if ($search != "") {
    $query .= " WHERE title LIKE '%$search%' OR content LIKE '%$search%'";
}

$query .= " ORDER BY id DESC LIMIT $start, $limit";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Welcome <?php echo $_SESSION['user']; ?> 👋</h3>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <!-- SEARCH FORM -->
    <form method="get" class="d-flex mb-4">
        <input type="text" name="search" class="form-control me-2"
               placeholder="Search posts..." value="<?php echo $search; ?>">
        <button class="btn btn-success" type="submit">Search</button>
    </form>

    <!-- ADD POST FORM -->
    <div class="card p-3 mb-4">
        <h4>Add New Post</h4>

        <form method="post">
            <input type="text" name="title" class="form-control mb-2" placeholder="Title" required>
            <textarea name="content" class="form-control mb-2" placeholder="Content" required></textarea>
            <button name="add" class="btn btn-primary">Add Post</button>
        </form>
    </div>

    <h4 class="mb-3">All Posts</h4>

    <!-- SHOW POSTS -->
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='card p-3 mb-3'>";
            echo "<h5>".$row['title']."</h5>";
            echo "<p>".$row['content']."</p>";
            echo "<a class='btn btn-sm btn-outline-danger' href='delete.php?id=".$row['id']."'>Delete</a>";
            echo "</div>";
        }
    } else {
        echo "<p class='text-muted'>No posts found.</p>";
    }
    ?>

    <!-- PAGINATION LINKS -->
    <nav>
        <ul class="pagination mt-4">

            <!-- Previous -->
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                <a class="page-link"
                   href="?search=<?php echo $search; ?>&page=<?php echo $page-1; ?>">Previous</a>
            </li>

            <!-- Page numbers -->
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                    <a class="page-link"
                       href="?search=<?php echo $search; ?>&page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php } ?>

            <!-- Next -->
            <li class="page-item <?php if($page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link"
                   href="?search=<?php echo $search; ?>&page=<?php echo $page+1; ?>">Next</a>
            </li>

        </ul>
    </nav>

</div>

</body>
</html>
