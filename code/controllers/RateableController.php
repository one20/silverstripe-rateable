<?php
/**
 * @author Shea Dawson <shea@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class RateableController extends Controller
{
    
    const URLSegment = 'rateable';

    private static $dependencies = array(
        'rateableService'    => '%$RateableService',
    );
    
    public static $allowed_actions = array(
        'rate',
        'clear'
    );

    /**
     * @var RateableService
     */
    public $rateableService;


    /**
     * action for rating an object
     * @return JSON
     **/
    public function rate($request)
    {
        $class    = $request->param('ObjectClassName');
        $id    = (int)$request->param('ObjectID');
        $score    = (int)$request->getVar('score');

        // check we have all the params
        if (!class_exists($class) || !$id || !$score || (!$object = $class::get()->byID($id))) {
            return Convert::raw2json(array(
                'status' => 'error',
                'message' => _t('RateableController.ERRORMESSAGE', 'Sorry, there was an error rating this item')
            ));
        }

        // check the object exists
        if (!$object && !$object->checkRatingsEnabled()) {
            return Convert::raw2json(array(
                'status' => 'error',
                'message' => _t('RateableController.ERRORNOTFOUNT', 'Sorry, the item you are trying to rate could not be found')
            ));
        }

        // check the user can rate the object
        if ($this->rateableService->userHasRated($class, $id)) {
            return Convert::raw2json(array(
                'status' => 'error',
                'message' => _t('RateableController.ERRORALREADYRATED', 'Sorry, You have already rated this item')
            ));
        }

        // create the rating
        $rating = Rating::create(array(
            'Score'        => $score,
            'ObjectID'        => $id,
            'ObjectClass'    => $class
        ));
        $rating->write();

        // success
        return Convert::raw2json(array(
            'status' => 'success',
            'averagescore' => $object->getAverageScore(),
            'userrating' => $score,
            'message' => _t('RateableController.THANKYOUMESSAGE', 'Thanks for rating!')
        ));
    }

    public function clear($request){
        $class    = $request->param('ObjectClassName');
        $id    = (int)$request->param('ObjectID');
        $object = $class::get()->byID($id);

        $userRating = DataObject::get('Rating')->filter(array(
            'MemberID' => Member::currentUserID(),
            'ObjectID' => $id
        ));

        $userRating = $userRating[0];
        $userRating->delete();

        //$userRating = $object->filter(array('MemberID' => Member::currentUserID()));

        return Convert::raw2json(array(
            'status' => 'success',
            'averagescore' => $object->getAverageScore(),
            'userID' => 1
        ));
    }
}
