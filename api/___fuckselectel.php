<?php
require "@imports.php";

$_stmt = $db_mysqli->prepare(
    "SELECT id, another_images, main_image, video1, video2, video3 from animals"
);

$_stmt->execute();
$_result = $_stmt->get_result();

while ($_row = $_result->fetch_array()) {
    $another_images = json_decode($_row["another_images"]);
    @mkdir("media/pets/" . $_row["id"]);
    if ($_row["video1"]) {
        file_put_contents(
            "media/pets/" . $_row["id"] . "/video1.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/pets/" .
                    $_row["id"] .
                    "/video1.mp4",
                "r"
            )
        );
    }
    if ($_row["video2"]) {
        file_put_contents(
            "media/pets/" . $_row["id"] . "/video2.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/pets/" .
                    $_row["id"] .
                    "/video2.mp4",
                "r"
            )
        );
    }
    if ($_row["video3"]) {
        file_put_contents(
            "media/pets/" . $_row["id"] . "/video3.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/pets/" .
                    $_row["id"] .
                    "/video3.mp4",
                "r"
            )
        );
    }
    file_put_contents(
        "media/pets/" . $_row["id"] . "/main.jpeg",
        fopen(
            "https://140706.selcdn.ru/lastangel/media/pets/" .
                $_row["id"] .
                "/main.jpeg",
            "r"
        )
    );
    //file_put_contents(
    //	"media/pets/".$_row['id']."/main_1.jpeg",
    //	fopen("https://140706.selcdn.ru/lastangel/media/pets/".$_row['id']."/main_1.jpeg", 'r')
    //);
    //file_put_contents(
    //	"media/pets/".$_row['id']."/main_square.jpeg",
    //	fopen("https://140706.selcdn.ru/lastangel/media/pets/".$_row['id']."/main_square.jpeg", 'r')
    //);
    //file_put_contents(
    //	"media/pets/".$_row['id']."/main_square2.jpeg",
    //	fopen("https://140706.selcdn.ru/lastangel/media/pets/".$_row['id']."/main_square2.jpeg", 'r')
    //);
    if (count($another_images) > 0) {
        foreach ($another_images as $another_image) {
            file_put_contents(
                "media/pets/" .
                    $_row["id"] .
                    "/another_" .
                    $another_image .
                    ".jpeg",
                fopen(
                    "https://140706.selcdn.ru/lastangel/media/pets/" .
                        $_row["id"] .
                        "/another_" .
                        $another_image .
                        ".jpeg",
                    "r"
                )
            );
            //file_put_contents(
            //	"media/pets/".$_row['id']."/another_".$another_image."_1.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/pets/".$_row['id']."/another_".$another_image."_1.jpeg", 'r')
            //);
            //file_put_contents(
            //	"media/pets/".$_row['id']."/another_".$another_image."_2.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/pets/".$_row['id']."/another_".$another_image."_2.jpeg", 'r')
            //);
        }
    }
}

$_stmt = $db_mysqli->prepare(
    "SELECT id, another_images, main_image, video1, video2, video3 from collections"
);

$_stmt->execute();
$_result = $_stmt->get_result();
while ($_row = $_result->fetch_array()) {
    $another_images = json_decode($_row["another_images"]);
    @mkdir("media/collections/" . $_row["id"]);
    if ($_row["video1"]) {
        file_put_contents(
            "media/collections/" . $_row["id"] . "/video1.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/collections/" .
                    $_row["id"] .
                    "/video1.mp4",
                "r"
            )
        );
    }
    if ($_row["video2"]) {
        file_put_contents(
            "media/collections/" . $_row["id"] . "/video2.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/collections/" .
                    $_row["id"] .
                    "/video2.mp4",
                "r"
            )
        );
    }
    if ($_row["video3"]) {
        file_put_contents(
            "media/collections/" . $_row["id"] . "/video3.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/collections/" .
                    $_row["id"] .
                    "/video3.mp4",
                "r"
            )
        );
    }
    file_put_contents(
        "media/collections/" . $_row["id"] . "/main.jpeg",
        fopen(
            "https://140706.selcdn.ru/lastangel/media/collections/" .
                $_row["id"] .
                "/main.jpeg",
            "r"
        )
    );
    //file_put_contents(
    //	"media/collections/".$_row['id']."/main_1.jpeg",
    //	fopen("https://140706.selcdn.ru/lastangel/media/collections/".$_row['id']."/main_1.jpeg", 'r')
    //);
    //file_put_contents(
    //	"media/collections/".$_row['id']."/main_square.jpeg",
    //	fopen("https://140706.selcdn.ru/lastangel/media/collections/".$_row['id']."/main_square.jpeg", 'r')
    //);
    //file_put_contents(
    //	"media/collections/".$_row['id']."/main_square2.jpeg",
    //	fopen("https://140706.selcdn.ru/lastangel/media/collections/".$_row['id']."/main_square2.jpeg", 'r')
    //);
    if (count($another_images) > 0) {
        foreach ($another_images as $another_image) {
            file_put_contents(
                "media/collections/" .
                    $_row["id"] .
                    "/another_" .
                    $another_image .
                    ".jpeg",
                fopen(
                    "https://140706.selcdn.ru/lastangel/media/collections/" .
                        $_row["id"] .
                        "/another_" .
                        $another_image .
                        ".jpeg",
                    "r"
                )
            );
            //file_put_contents(
            //	"media/collections/".$_row['id']."/another_".$another_image."_1.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/collections/".$_row['id']."/another_".$another_image."_1.jpeg", 'r')
            //);
            //file_put_contents(
            //	"media/collections/".$_row['id']."/another_".$another_image."_2.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/collections/".$_row['id']."/another_".$another_image."_2.jpeg", 'r')
            //);
        }
    }
}

$_stmt = $db_mysqli->prepare("SELECT another_images from mainphotoalbum");

$_stmt->execute();
$_result = $_stmt->get_result();
while ($_row = $_result->fetch_array()) {
    $another_images = json_decode($_row["another_images"]);

    if (count($another_images) > 0) {
        foreach ($another_images as $another_image) {
            file_put_contents(
                "media/mainphotoalbum/another_" . $another_image . ".jpeg",
                fopen(
                    "https://140706.selcdn.ru/lastangel/media/mainphotoalbum/another_" .
                        $another_image .
                        ".jpeg",
                    "r"
                )
            );
            //file_put_contents(
            //	"media/mainphotoalbum/".$_row['id']."/another_".$another_image."_1.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/mainphotoalbum/".$_row['id']."/another_".$another_image."_1.jpeg", 'r')
            //);
            //file_put_contents(
            //	"media/mainphotoalbum/".$_row['id']."/another_".$another_image."_2.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/mainphotoalbum/".$_row['id']."/another_".$another_image."_2.jpeg", 'r')
            //);
        }
    }
}

$_stmt = $db_mysqli->prepare("SELECT another_images from documents");

$_stmt->execute();
$_result = $_stmt->get_result();
while ($_row = $_result->fetch_array()) {
    $another_images = json_decode($_row["another_images"]);

    if (count($another_images) > 0) {
        foreach ($another_images as $another_image) {
            file_put_contents(
                "media/documents/another_" . $another_image . ".jpeg",
                fopen(
                    "https://140706.selcdn.ru/lastangel/media/documents/another_" .
                        $another_image .
                        ".jpeg",
                    "r"
                )
            );
            //file_put_contents(
            //	"media/documents/".$_row['id']."/another_".$another_image."_1.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/documents/".$_row['id']."/another_".$another_image."_1.jpeg", 'r')
            //);
            //file_put_contents(
            //	"media/documents/".$_row['id']."/another_".$another_image."_2.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/documents/".$_row['id']."/another_".$another_image."_2.jpeg", 'r')
            //);
        }
    }
}

$_stmt = $db_mysqli->prepare("SELECT another_images from clinic_photos");

$_stmt->execute();
$_result = $_stmt->get_result();
while ($_row = $_result->fetch_array()) {
    $another_images = json_decode($_row["another_images"]);

    if (count($another_images) > 0) {
        foreach ($another_images as $another_image) {
            file_put_contents(
                "media/clinicPhotos/another_" . $another_image . ".jpeg",
                fopen(
                    "https://140706.selcdn.ru/lastangel/media/clinicPhotos/another_" .
                        $another_image .
                        ".jpeg",
                    "r"
                )
            );
            //file_put_contents(
            //	"media/collections/".$_row['id']."/another_".$another_image."_1.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/clinicPhotos/".$_row['id']."/another_".$another_image."_1.jpeg", 'r')
            //);
            //file_put_contents(
            //	"media/collections/".$_row['id']."/another_".$another_image."_2.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/clinicPhotos/".$_row['id']."/another_".$another_image."_2.jpeg", 'r')
            //);
        }
    }
}

$_stmt = $db_mysqli->prepare(
    "SELECT id, another_images, video1, video2, video3 from news"
);

$_stmt->execute();
$_result = $_stmt->get_result();
while ($_row = $_result->fetch_array()) {
    $another_images = json_decode($_row["another_images"]);
    @mkdir("media/news/" . $_row["id"]);
    if ($_row["video1"]) {
        file_put_contents(
            "media/news/" . $_row["id"] . "/video1.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/news/" .
                    $_row["id"] .
                    "/video1.mp4",
                "r"
            )
        );
    }
    if ($_row["video2"]) {
        file_put_contents(
            "media/news/" . $_row["id"] . "/video2.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/news/" .
                    $_row["id"] .
                    "/video2.mp4",
                "r"
            )
        );
    }
    if ($_row["video3"]) {
        file_put_contents(
            "media/news/" . $_row["id"] . "/video3.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/news/" .
                    $_row["id"] .
                    "/video3.mp4",
                "r"
            )
        );
    }

    if (count($another_images) > 0) {
        foreach ($another_images as $another_image) {
            file_put_contents(
                "media/news/" .
                    $_row["id"] .
                    "/another_" .
                    $another_image .
                    ".jpeg",
                fopen(
                    "https://140706.selcdn.ru/lastangel/media/news/" .
                        $_row["id"] .
                        "/another_" .
                        $another_image .
                        ".jpeg",
                    "r"
                )
            );
            //file_put_contents(
            //	"media/news/".$_row['id']."/another_".$another_image."_1.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/news/".$_row['id']."/another_".$another_image."_1.jpeg", 'r')
            //);
            //file_put_contents(
            //	"media/news/".$_row['id']."/another_".$another_image."_2.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/news/".$_row['id']."/another_".$another_image."_2.jpeg", 'r')
            //);
        }
    }
}

$_stmt = $db_mysqli->prepare(
    "SELECT id, another_images, video1, video2, video3 from stories"
);

$_stmt->execute();
$_result = $_stmt->get_result();
while ($_row = $_result->fetch_array()) {
    $another_images = json_decode($_row["another_images"]);
    @mkdir("media/stories/" . $_row["id"]);
    if ($_row["video1"]) {
        file_put_contents(
            "media/stories/" . $_row["id"] . "/video1.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/stories/" .
                    $_row["id"] .
                    "/video1.mp4",
                "r"
            )
        );
    }
    if ($_row["video2"]) {
        file_put_contents(
            "media/stories/" . $_row["id"] . "/video2.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/stories/" .
                    $_row["id"] .
                    "/video2.mp4",
                "r"
            )
        );
    }
    if ($_row["video3"]) {
        file_put_contents(
            "media/stories/" . $_row["id"] . "/video3.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/stories/" .
                    $_row["id"] .
                    "/video3.mp4",
                "r"
            )
        );
    }

    if (count($another_images) > 0) {
        foreach ($another_images as $another_image) {
            file_put_contents(
                "media/stories/" .
                    $_row["id"] .
                    "/another_" .
                    $another_image .
                    ".jpeg",
                fopen(
                    "https://140706.selcdn.ru/lastangel/media/stories/" .
                        $_row["id"] .
                        "/another_" .
                        $another_image .
                        ".jpeg",
                    "r"
                )
            );
            //file_put_contents(
            //	"media/stories/".$_row['id']."/another_".$another_image."_1.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/stories/".$_row['id']."/another_".$another_image."_1.jpeg", 'r')
            //);
            //file_put_contents(
            //	"media/stories/".$_row['id']."/another_".$another_image."_2.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/stories/".$_row['id']."/another_".$another_image."_2.jpeg", 'r')
            //);
        }
    }
}

$_stmt = $db_mysqli->prepare(
    "SELECT id, another_images, video1, video2, video3 from acquaintanceship"
);

$_stmt->execute();
$_result = $_stmt->get_result();
while ($_row = $_result->fetch_array()) {
    $another_images = json_decode($_row["another_images"]);
    @mkdir("media/acquaintanceship");
    if ($_row["video1"]) {
        file_put_contents(
            "media/acquaintanceship/video1.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/acquaintanceship/video1.mp4",
                "r"
            )
        );
    }
    if ($_row["video2"]) {
        file_put_contents(
            "media/acquaintanceship/video2.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/acquaintanceship/video2.mp4",
                "r"
            )
        );
    }
    if ($_row["video3"]) {
        file_put_contents(
            "media/acquaintanceship/video3.mp4",
            fopen(
                "https://140706.selcdn.ru/lastangel/media/acquaintanceship/video3.mp4",
                "r"
            )
        );
    }

    if (count($another_images) > 0) {
        foreach ($another_images as $another_image) {
            file_put_contents(
                "media/acquaintanceship/another_" . $another_image . ".jpeg",
                fopen(
                    "https://140706.selcdn.ru/lastangel/media/acquaintanceship/another_" .
                        $another_image .
                        ".jpeg",
                    "r"
                )
            );
            //file_put_contents(
            //	"media/acquaintanceship/another_".$another_image."_1.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/acquaintanceship/another_".$another_image."_1.jpeg", 'r')
            //);
            //file_put_contents(
            //	"media/acquaintanceship/another_".$another_image."_2.jpeg",
            //	fopen("https://140706.selcdn.ru/lastangel/media/acquaintanceship/another_".$another_image."_2.jpeg", 'r')
            //);
        }
    }
}
?>
