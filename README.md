# Video Toolkit

Video toolkit provides an easy way to embed videos in your templates. Videos can be from YouTube, Vimeo or a local asset. The plugin fetches extra data like thumbnail and video ratio from oEmbed behind the scenes and caches the result for performance. Supports Vimeo unlisted or private videos. Add responsive wrapper 

You can get a video embed, responsive video embed, thumbnail url, or thumbnail image.

## Features
- Embed videos from YouTube, Vimeo, or local assets with a simple function call.
- Fetch extra data like thumbnail and video ratio from oEmbed behind the scenes and cache the result for performance.
- Add autoplay, loop, and muted options to the video embed. also works with Vimeo background
- Supports Vimeo unlisted videos

## Usage

### Simple video embed

```twig
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0') }}
{{ videoToolkit('https://vimeo.com/1084537') }}
{{ videoToolkit('https://example.com/path/to/video.mp4') }}
{{ videoToolkit('/path/to/video.mp4') }}
{{ videoToolkit(asset) }}
```

### Advanced video embed

```twig
{% set options = {
    'autoplay': true,
    'loop': true,
    'muted': true,
    'responsive': true,
} %}
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', options) }}
{{ videoToolkit('https://vimeo.com/1084537', options) }}
{{ videoToolkit('https://example.com/path/to/video.mp4', options) }}
{{ videoToolkit('/path/to/video.mp4', options) }}
{{ videoToolkit(asset, options) }}
```
### Embed Url with options
    
```twig
{% set options = {
    'return': 'embedUrl', 
    'autoplay': true, 
    'loop': true, 
    'muted': true
} %}
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', options) }}
{{ videoToolkit('https://vimeo.com/1084537', options) }}
```

### Thumbnail

You can fetch the thumbnail url or thumbnail image from Vimeo and YouTube. You can also do it for local videos, but you need to provide the thumbnail url manually, see options below.

#### YouTube Thumbnail url
```twig
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', {'return': 'thumbnailUrl'}) }}
```
#### Vimeo Thumbnail url
```twig
{{ videoToolkit('https://vimeo.com/1084537',{'return': 'thumbnailUrl'}) }}
```
#### Youtube Thumbnail with img tag
```twig
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', {'return': 'thumbnail'}) }}
```
#### Vimeo Thumbnail with img tag

```twig
{{ videoToolkit('https://vimeo.com/1084537',{'return': 'thumbnail'}) }}
```

## Video Types supported
- YouTube
- Vimeo
- mp4 from local asset, url or local path

## Options

### Return
You can return the video embed code, video embed url, thumbnail url, or thumbnail image. The default is the video embed.

```twig
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', {'return': 'video'}) }}
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', {'return': 'videoUrl'}) }}
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', {'return': 'thumbnailUrl'}) }}
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', {'return': 'thumbnail'}) }}
```
### Width
Works for all video types. It will add the width attribute to the video embed.

```twig
{{ videoToolkit(video, {'width': 640}) }}
```

### Height
Works for all video types. It will add the height attribute to the video embed.

```twig
{{ videoToolkit(video, {'height': 360}) }}
```

### Autoplay
Works for all video types. It will add the autoplay attribute to the video embed.

```twig
{{ videoToolkit(video, {'autoplay': true}) }}
```

### Loop
Works for all video types except YouTube. It will add the loop attribute to the video embed.

```twig
{{ videoToolkit(video, {'loop': true}) }}
```
### Muted
Works for all video types. It will add the muted attribute to the video embed.

```twig
{{ videoToolkit(video, {'muted': true}) }}
```

### Controls
Works for all video types. Show or hide the controls. This will set the background parameter to Vimeo embeds.

```twig
{{ videoToolkit(video, {'controls': true}) }}
```

### Poster
For mp4 videos, you can add a poster image. It will add the poster attribute to the video embed. thumbnailUrl must be set to the image used as the poster.

```twig
{{ videoToolkit(video, {'poster': true}) }}
```

### Thumbnail Url
For mp4 videos, you can add a thumbnail image. This can be used as a poster image, and it can be fetched with the `return: 'thumbnailUrl'` and `return: 'thumbnail'` option.

```twig
{{ videoToolkit(video, {'thumbnailUrl': 'https://example.com/path/to/thumbnail.jpg'}) }}
```

### Responsive
Adds a responsive wrapper around the video embed. The wrapper has styles to make it responsive. You can replace the styles with your own styles or classes. This can be controlled with the customStyles, customCss and useStyles options. if useProviderRatio is true, it will use the ratio fetched from the YouTube or Vimeo.

```twig
{{ videoToolkit(video, {'responsive': true}) }}
```

### Use Provider Ratio
If responsive is true, it will use the ratio fetched from the YouTube or Vimeo. If you want to use your own ratio, you can set width and height.

```twig
{{ videoToolkit(video, {'responsive': true, 'useProviderRatio': true}) }}
```

### No cookie
To embed a Vimeo video without cookies, you can use the noCookie option. This will add the `dnt` parameter to the Vimeo embed url and use youtube-nocookie.com for YouTube embeds.

```twig
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', {'noCookie': true}) }}
```

### Use Styles
If responsive is true, it will use the default styles. If you want to use your own styles, you can set useStyles to false and add your own styles or classes with customStyles and customCss. If not set customStyles will be added to predefined styles.

```twig
{{ videoToolkit(video, {'responsive': true, 'useStyles': false}) }}
```

### Custom Css
If responsive is true, you can add custom css styles to the responsive wrapper. you must specify where you want to add the css styles, `wrapper`, `wrapperInner` or `iframe`. css styles are added as a key value pair 

```twig
{% set options = {
    'responsive': true,
    'useStyles': false,
    'customCss': {
        'wrapper': {
           'background-image': 'url(https://example.com/path/to/background.jpg)',
           'background-size': 'cover'
        },
        'iframe': {
            'border': 'none'
        }
    }
} %}
{{ videoToolkit(video, options) }}
```
### Custom Classes
If responsive is true, you can add custom classes to the responsive wrapper. you must specify where you want to add the classes, `wrapper`, `wrapperInner` or `iframe`. classes is added as an array of strings.

```twig
{% set options = {
    'responsive': true,
    'useStyles': false,
    'customCss': {
        'wrapper': {'video-wrapper', 'example-class'},
        'iframe': {'video-iframe', 'example-class'}
    }
} %}
{{ videoToolkit(video, options) }}
```

## Examples

### Responsive video using provider ratio
```twig
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', {'responsive': true, 'useProviderRatio': true}) }}
{{ videoToolkit('https://vimeo.com/1084537', {'responsive': true, 'useProviderRatio': true}) }}
```

### Responsive video with custom styles
```twig
{% set options = {
    'responsive': true,
    'useStyles': false,
    'customCss': {
        'wrapper': {
           'background-image': 'url(https://example.com/path/to/background.jpg)',
           'background-size': 'cover'
        },
        'iframe': {
            'border': 'none'
        }
    }
} %}
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', options) }}
```

### Responsive video with custom classes
```twig
{% set options = {
    'responsive': true,
    'useStyles': false,
    'customCss': {
        'wrapper': {'video-wrapper', 'example-class'},
        'iframe': {'video-iframe', 'example-class'}
    }
} %}
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', options) }}
```

### Mp4 video with poster
```twig
{% set options = {
    'thumbnailUrl': 'https://example.com/path/to/poster.jpg',
    'poster': true
} %}
{{ videoToolkit('https://example.com/path/to/video.mp4', options) }}
```

### Video embed with autoplay, loop, and muted
```twig
{% set options = {
    'autoplay': true,
    'loop': true,
    'muted': true
} %}
{{ videoToolkit('https://www.youtube.com/watch?v=9bZkp7q19f0', options) }}
{{ videoToolkit('https://vimeo.com/1084537', options) }}
{{ videoToolkit('https://example.com/path/to/video.mp4', options) }}
```

## Requirements

This plugin requires Craft CMS 4.5.0 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Video Toolkit”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require solvras/craft-video-toolkit

# tell Craft to install the plugin
./craft plugin/install video-toolkit
```
