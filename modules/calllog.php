<?php

try {
    
    $maxRetrieveTimeSpan = $_ENV['RC_maxRetrieveTimespan'];

    $dateFromTime = $global_currentTime - $global_timeOffset - $maxRetrieveTimeSpan;
    $dateToTime = $global_currentTime - $global_timeOffset;
    
    if(isset($global_appData['lastRunningTime'])){
        $dateFromTime = $global_appData['lastRunningTime'] - $global_timeOffset + 1;
    }

    function getCallLogs($platform, $dateFromTime, $dateToTime) {
        try{
            return requestMultiPages($platform, '/account/~/call-log', array(
                'withRecording' => 'True',
                'dateFrom' => date('Y-m-d\TH:i:s\Z', $dateFromTime),
                'dateTo' => date('Y-m-d\TH:i:s\Z', $dateToTime),
                'type' => 'Voice',
                'perPage' => 1000
            ));
        }
        catch(Exception $e){
            $diff = floor(($dateToTime - $dateFromTime + 1) / 2);
            if($diff < 300) {
                throw $e;
            }else {
                return array_merge(getCallLogs($platform, $dateFromTime, $dateFromTime + $diff), 
                    getCallLogs($platform, $dateFromTime + $diff + 1, $dateToTime));       
            }
        }
    }

    rcLog($global_logFile, 1, 'Start to load Call Logs from '.date('Y-m-d H:i:s', $dateFromTime).' to '.date('Y-m-d H:i:s', $dateToTime).'!');
    $global_callLogs = getCallLogs($platform, $dateFromTime, $dateToTime);
    
    rcLog($global_logFile, 1, count($global_callLogs).' Call Logs Loaded!');
    if(count($global_callLogs) > 0) {
        foreach ($global_callLogs as $callLog) {
            rcLog($global_logFile, 0, $callLog->uri);
        }
    }
    
} catch (Exception $e) {
    rcLog($global_logFile, 2, 'Error occurs when retrieving call logs -> ' . $e->getMessage());
    throw $e;    
}	



