<?php
namespace Cora;

class Paginate
{
    protected $url;
    protected $totalQuantity;
    protected $itemsPerPage;
    protected $offset;
    protected $filters;

    public function __construct($load, $url, $filtersArray, $totalQuantity, $currentOffset = 0, $itemsPerPage = 18)
    {
        $this->load             = $load;
        $this->url              = $url;
        $this->filtersArray     = $filtersArray;
        $this->totalQuantity    = $totalQuantity;
        $this->offset           = $currentOffset;
        $this->itemsPerPage     = $itemsPerPage

        // Setup Descriptions for the filters.
        $this->filters = [
           'letterFilter' => 'Only showing %s that start with "%s"',
           'query' => 'Only showing %s that contain "%s"'
        ];
    }


    public function showNext($offset = false)
    {
        // Check if a specific offset was passed in, otherwise use data member.
        if ($offset === false) {
            $offset = $this->offset;
        }

        // Note: Offsets start with ZERO!
        // The Offset being 0 means this evaluates to 0+1, which means you are on the first page.
        $currentPageNumber = $offset+1;

        if ($this->totalQuantity > $currentPageNumber * $this->itemsPerPage) {
            return true;
        }
        return false;
    }


    public function showPrevious($offset = false)
    {
        // Check if a specific offset was passed in, otherwise use data member.
        if ($offset === false) {
            $offset = $this->offset;
        }

        if ($offset > 0) {
            return true;
        }
        return false;
    }


    public function getNumPages()
    {
        $result = 0;

        if ($this->totalQuantity > 0) {
            $result = ceil($this->totalQuantity / $this->itemsPerPage);
        }
        return $result;
    }


    public function showOffset($offset)
    {
        if ($offset >= 0 && $this->totalQuantity > $offset * $this->itemsPerPage) {
            return true;
        }
        return false;
    }


    public function showRelOffset($num = 0)
    {
        return $this->showOffset($this->offset + $num);
    }


    public function getOffset($num = 0)
    {
        return ($this->offset + $num);
    }


    public function getCalcLink($exclude = false)
    {
        // Turn Exclude into an array
        if ($exclude == false) {
            $exclude = array();
        }
        else if (!is_array($exclude)) {
            $exclude = array($exclude);
        }

        // Setup
        $url = $this->url;
        $filters = count($this->filtersArray);
        $i = 1;

        $url .= '?';
        if ($filters > 0) {
            foreach ($this->filtersArray as $key => $value) {
                if (!in_array($key, $exclude)) {
                    $url .= $key.'='.$value;
                    if ($i != $filters) {
                        $url .= '&';
                    }
                }
                $i++;
            }
        }
        return $url;
    }


    public function getLink($offset)
    {
        return $this->getCalcLink().'&offset='.$offset;
    }


    public function getBaseLink()
    {
        return $this->url;
    }


    public function getFilterLink($value, $filterName = 'letterFilter')
    {
        return $this->getCalcLink(['letterFilter']).'&'.$filterName.'='.$value;
    }


    public function getRelLink($num)
    {
        //return $this->url.'?offset='.($this->offset+$num);
        return $this->getCalcLink().'&offset='.($this->offset+$num);
    }


    public function getSortLink($orderBy)
    {
        if ($orderBy == $this->filtersArray['orderBy']) {
            if ($this->filtersArray['orderDir'] == 'asc') {
                $orderDir = 'desc';
            }
            else {
                $orderDir = 'asc';
            }
        }
        else {
            $orderDir = 'asc';
        }
        return $this->getCalcLink(['orderBy', 'orderDir']).'&orderBy='.$orderBy.'&orderDir='.$orderDir;
    }


    public function getCalcInputs($exclude = false)
    {
        // Turn Exclude into an array
        if ($exclude == false) {
            $exclude = array();
        }
        else if (!is_array($exclude)) {
            $exclude = array($exclude);
        }

        // Setup
        $items = '';
        $filters = count($this->filtersArray);

        if ($filters > 0) {
            foreach ($this->filtersArray as $key => $value) {
                if (!in_array($key, $exclude)) {
                    $items .= '<input name="'.$key.'" type="hidden" value="'.$value.'">';
                }
            }
        }
        return $items;
    }


    public function display($bool, $displayIfTrue = '', $displayIfFalse = 'hide')
	{
		if(!$bool) {
			return $displayIfFalse;
		}
		else
			return $displayIfTrue;
	}


    public function active($bool)
	{
		if(!$bool) {
			return 'disabled';
		}
		else
			return '';
	}


    /**
     *  Returns an array with messages to inform the end-user what filters they have active.
     *  Will look in the Filters array for letterFilters or search queries that are limiting the result set.
     *  In order to return a user-friendly description of what filters are active, it requires an array of column
     *  descriptions. See example below.
     *
     *  $columnDescriptions = ['practices.name' => 'practices'];
     *  $this->filtersArray = ['query' => 'Happy'];
     *  $this->filters = ['query' => 'Showing %s that contain "%s"'];
     *  RETURNED RESULT: ['Showing practices that contain "Happy"']
     */
     public function getFilters($columnDescriptions = [], $activeFieldName = false)
     {
         $filters = [];

         if (count($this->filtersArray) > 0) {
             foreach ($this->filtersArray as $key => $value) {
                 if (isset($this->filters[$key])) {

                     // Filters are active on the active data column
                     $activeFieldName = $activeFieldName?: $this->filtersArray['orderBy'];

                     // There may optionally be passed in a more human friendly name for the active field.
                     $friendlyFieldName = isset($columnDescriptions[$activeFieldName]) ? $columnDescriptions[$activeFieldName] : $activeFieldName;

                     $filters[] = sprintf($this->filters[$key], $friendlyFieldName, $value);
                     //$filters[] = call_user_func_array("sprintf", [$this->filters[$key], ]);
                 }
             }
         }
         return $filters;
     }


    public function view($url = '_partials/paginate')
    {
        $data = array();
        $data['paginate'] = $this;
        return $this->load->view($url, $data, true);
    }
}
