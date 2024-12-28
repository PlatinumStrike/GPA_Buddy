<?php

class Schedule
{
    public $term = ["Year" => null, "Semester" => null];
    public $courses = [];

    function __construct($term)
    {
        $this->term['Year'] = intval(explode(" ", $term)[0]);
        $this->term['Semester'] = explode(" ", $term)[1];
    }

    function addCourse($course)
    {
        if (!($course instanceof Course)) {
            throw new InvalidArgumentException("The event must be an instance of Component");
        }
        $this->courses[] = $course;
    }
}

class Course
{
    public $components = [];
    public $dept;
    public $course_code;

    function __construct($dept, $course_code)
    {
        $this->dept = $dept;
        $this->course_code = new CourseCode($course_code);
    }

    function addComponent($component)
    {
        if (!($component instanceof Component)) {
            throw new InvalidArgumentException("The event must be an instance of Component");
        }
        $this->components[] = $component;
    }
}

class CourseCode
{
    public $level;
    public $id;
    public $credits;

    function __construct($code_str)
    {
        $level = intval($code_str[0]);
        $id = intval(substr($code_str, 1, 2));
        $credits = intval($code_str[3]);
    }
}

class Component
{
    public $class_number;
    public $section = ["Prefix" => null, "Number" => null];
    public $type;
    public $events = [];
    public $date = ["start" => null, "end" => null];

    function __construct($class_number, $section, $type, $dates)
    {
        $this->class_number = intval($class_number);
        $this->section['Prefix'] = substr($section, 0, 1);
        $this->section['Number'] = intval(substr($section, 1, 2));
        $this->type = $type;
    }

    function addEvent($event)
    {
        if (!($event instanceof WeeklyEvent)) {
            throw new InvalidArgumentException("The event must be an instance of WeeklyEvent");
        }
        $this->events[] = $event;
    }
}

class WeeklyEvent
{
    public $week_days = ["MO" => false, "TU" => false, "WE" => false, "TH" => false, "FR" => false, "SA" => false, "SU" => false];
    public $time = ["start" => null, "end" => null];
    public $location = ["Building" => null, "Room" => null, "Address" => null];

    function __construct($week_days, $start_time, $end_time, $building, $room)
    {
        include("inc/models/addresses.php");
        // Populate days array
        foreach (preg_split("/(?=[A-Z])(?![a-z])/", $week_days) as $day) {
            $this->week_days[strtoupper($day)] = true;
        }

        // Convert the times to objects then populate
        $this->time["start"] = new EventTime($start_time);
        $this->time["end"] = new EventTime($end_time);


        // Populate Location
        $room_list = explode(" ", $room);
        $this->location["Building"] = $room_list[0];
        $this->location["Room"] = $room_list[1];
        $this->location["Address"] = $ADDRESSES[$room_list[0]];
    }
}

class EventTime
{
    public $hour;
    public $minute;

    function __construct($time_str)
    {
        // Parse the timestring and express it as a 24-hour time.
        $matches = [];
        preg_match("/^(\d+):(\d+)(AM|PM)$/", $time_str, $matches);
        $this->hour = (int) $matches[1];
        $this->minute = (int) $matches[2];
        if ($matches[3] == "AM") {
            if ($this->hour == 12) {
                $this->hour = 0;
            }
        } else {
            if ($this->hour != 12) {
                $this->hour += 12;
            }
        }
    }
}
