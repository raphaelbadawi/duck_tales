<?php

namespace App\Service;

class UrlHelper
{
    private function parseUrlThumbnail(array $urlMatch)
    {
        // parses the url
        $url = htmlspecialchars(trim($urlMatch[0]));
        $urlData = parse_url($url);
        $host = $urlData['host'];
        $file = fopen($url, 'r');

        // adds it to a string content
        $content = '';
        while (!feof($file)) {
            $content .= fgets($file, 1024);
        }

        // get meta tags as an entrypoint to relevant informations
        $meta_tags = get_meta_tags($url);

        // gets the title
        $title = '';

        if (array_key_exists('og:title', $meta_tags)) {
            $title = $meta_tags['og:title'];
        } else if (array_key_exists('twitter:title', $meta_tags)) {
            $title = $meta_tags['twitter:title'];
        } else {
            $title_pattern = '/<title>(.+)<\/title>/i';
            preg_match_all($title_pattern, $content, $title);

            if (!is_array($title[1])) {
                $title = $title[1];
            } else {
                if (count($title[1]) > 0) {
                    $title = $title[1][0];
                } else {
                    $title = 'Title not found!';
                }
            }
        }

        $title = ucfirst($title);

        // get the description
        $desc = '';

        if (array_key_exists('description', $meta_tags)) {
            $desc = $meta_tags['description'];
        } else if (array_key_exists('og:description', $meta_tags)) {
            $desc = $meta_tags['og:description'];
        } else if (array_key_exists('twitter:description', $meta_tags)) {
            $desc = $meta_tags['twitter:description'];
        } else {
            $desc = 'Description not found!';
        }

        $desc = ucfirst($desc);

        // get the picture
        $img_url = '';

        if (array_key_exists('og:image', $meta_tags)) {
            $img_url = $meta_tags['og:image'];
        } else if (array_key_exists('og:image:src', $meta_tags)) {
            $img_url = $meta_tags['og:image:src'];
        } else if (array_key_exists('twitter:image', $meta_tags)) {
            $img_url = $meta_tags['twitter:image'];
        } else if (array_key_exists('twitter:image:src', $meta_tags)) {
            $img_url = $meta_tags['twitter:image:src'];
        } else {
            // image not found in meta tags so find it from content
            $img_pattern = '/<img[^>]*' . 'src=[\"|\'](.*)[\"|\']/Ui';
            $images = '';
            preg_match_all($img_pattern, $content, $images);

            $total_images = is_array($images[1]) ? count($images[1]) : 0;
            if ($total_images > 0) {
                $images = $images[1];
                for ($i = 0; $i < $total_images; $i++) {
                    if ($images[$i][0] == "/" ? getimagesize("https://" . $host . $images[$i]) : getimagesize($images[$i])) {
                        list($width, $height, $type, $attr) = $images[$i][0] == "/" ? getimagesize("https://" . $host . $images[$i]) : getimagesize($images[$i]);
                        if ($width > 100) { // we don't want a mere icon, so we filter by width
                            $img_url = $images[$i][0] == "/" ? getimagesize("https://" . $host . $images[$i]) : getimagesize($images[$i]);
                        }
                        break;
                    }
                }
            }
        }
        $urlTag = "<div class='border-solid border-black border-2 rounded-sm block w-full'><a href='$url'><div>$title</div>";
        $urlTag .= "<div ><img class='w-32 mx-auto my-2 rounded-md' src='$img_url' alt='Picture preview'></div>";
        $urlTag .= "<div class='text-sm'>$desc</div>";
        $urlTag .= "<div>$host</div></a></div>";
        return $urlTag;
    }

    public function addUrlTagToContent(String $content): array|String|null
    {
        $pattern =  "/https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()!@:%_\+.~#?&\/\/=]*)/i";
        // $replacement = '<a href="$0">$0</a>';
        $content = preg_replace_callback($pattern, [$this, "parseUrlThumbnail"], $content);
        return $content;
    }
}
