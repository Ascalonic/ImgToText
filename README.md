# ImgToText
Image to Text converter using Google Vision API

Using Google Vision API for text detection:

```php
function detect_text($projectId, $path)
{
    $vision = new VisionClient([
		'keyFilePath' => 'cred.json',
        	'projectId' => $projectId
    ]);
    
    $image = $vision->image(file_get_contents($path), ['TEXT_DETECTION']);
    $result = $vision->annotate($image);
    
    foreach ((array) $result->text() as $text) {
		$ret=$text->description();
        	$app['monolog']->addDebug($text->description());
    }
}
```
