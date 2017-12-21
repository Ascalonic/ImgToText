<?php


require('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Google\Cloud\Vision\VisionClient;

use \Eventviva\ImageResize;

$projectId = 'cyatengine';

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

////////////////////////////////////////////////////////////////////////////////////////////

$app->get('/imgtotext', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

function detect_text($projectId, $path, $app)
{
    $vision = new VisionClient([
		'keyFilePath' => 'cred.json',
        'projectId' => $projectId
    ]);
    $image = $vision->image(file_get_contents($path), ['TEXT_DETECTION']);
    $result = $vision->annotate($image);
	
	//$app['monolog']->addDebug($path);
	//$app['monolog']->addDebug(print_r( $result, true ));
	
    //print("Texts:\n");
	
	$ret="";
	
    foreach ((array) $result->text() as $text) {
		$ret=$text->description();
        $app['monolog']->addDebug($text->description());
		break;
    }
	
	
	return nl2br($ret);
}

$app->post('/convert', function(Request $request) use($app) {
	
	$startx = $request->request->get('startx');	
	$starty = $request->request->get('starty');	
	$width = $request->request->get('width');	
	$height = $request->request->get('height');	
	
	session_start();
	
	$imgpath = $_SESSION['imgpath'];
	$im=null;
	
	$path_split = explode('.', $imgpath);
	$path_wo_ext = $path_split[0];
	
	$image = new ImageResize($imgpath);
	$image->freecrop($width, $height , $x =  $startx, $y = $starty);
	$image->save($path_wo_ext.'_cropped.jpg');
	
	//$app['monolog']->addDebug($path_wo_ext.'_cropped.jpg');
	
	$detected = detect_text($projectId, 'https://cyat.herokuapp.com/'.$path_wo_ext.'_cropped.jpg',$app);

	$ret=array('text'=>$detected);
	
	return json_encode($ret);
  
});



$app->run();
