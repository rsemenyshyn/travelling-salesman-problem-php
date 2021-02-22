<?php

namespace TSP\Algorithms;

use TSP\WeightedEdge;

/**
 * Brute Force algorithm
 */
class BruteForce implements AlgorithmInterface
{
    /** @var array */
    protected $edges = [];

    /** @var int */
    protected $minWeight = 0;

    /** @var array */
    protected $bestTour = [];

    /** @var int */
    protected $fixedStart = -1;

    /**
     * @param WeightedEdge[] $edges
     * @param int $size
     * @param int $order
     *
     * @return WeightedEdge[]|null
     */
    public function getTour(array $edges, $size, $order)
    {
        if (!$order) {
            return null;
        }
        $this->edges = $edges;
        $items = [];
        for ($i = 1; $i < $order; $i++) {
            $items[] = $i;
        }
        $this->permutations($items);
        return $this->bestTour;
    }

    public function setFixedStart($index) {
        $this->fixedStart = $index;
    }

    public function getFixedStart() {
        return $this->fixedStart;
    }


    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Brute Force Algorithm';
    }

    /**
     * @param array $items
     * @param array $perms
     */
    protected function permutations(array $items, array $perms = [])
    {
        if (empty($items)) { // if no items left to take
            array_unshift($perms, $this->edges[0]->getFirst());
            array_push($perms, $this->edges[0]->getFirst());
            $this->calculateTotalWeight($perms);
        }  else {
            for ($i = count($items) - 1; $i >= 0; --$i) { // for all remaining items
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1); // take 1 element from $newitems
                array_unshift($newperms, $foo); // put that element in front of $newperms
                $this->permutations($newitems, $newperms);
            }
        }
    }

    /**
     * @param array $perms
     */
    protected function calculateTotalWeight(array $perms)
    {
        $weight = 0;
        $tour = [];
        for($i = 0; $i < count($perms) - 1; $i++) {
            /** @var WeightedEdge $weightedEdge */
            foreach($this->edges as $weightedEdge) {
                if (($weightedEdge->getFirst() == $perms[$i] && $weightedEdge->getSecond() == $perms[$i+1]) ||
                    ($weightedEdge->getFirst() == $perms[$i+1] && $weightedEdge->getSecond() == $perms[$i])) {
                    $tour[] = $weightedEdge;
                    $weight += $weightedEdge->getWeight();
                    break;
                }
            }
        }
        $this->checkAndSaveTour($tour, $weight);
    }

    protected function checkAndSaveTour($tour, $weight) {
        $startPointIsFine = true;
        $endPointIsFine = true;
        if ($this->fixedStart >= 0) {
            /** @var WeightedEdge $edgeFirst */
            /** @var WeightedEdge $edgeSecond */
            /** @var WeightedEdge $edgeBeforeLast */
            /** @var WeightedEdge $edgeLast */
            $edgeFirst = reset($tour);
            $edgeSecond = count($tour) > 1 ? $tour[1] : null;
            if ($edgeFirst && $edgeSecond) {
                // edges are point-point-...: .-.-.-
                // the second edge has one point from first also (chain example above)
                // if start is fixed, it shold be in points of first edge, but not in second edge
                $startPointIsFine = in_array($this->fixedStart, $edgeFirst->getPoints()) && !in_array($this->fixedStart, $edgeSecond->getPoints());
            }
            $edgeLast = count($tour) > 1 ? $tour[count($tour) - 1] : null;
            $edgeBeforeLast = count($tour) > 2 ? $tour[count($tour) - 2] : null;
            if($edgeBeforeLast && $edgeLast) {
                // the logic is similar to the one with Start Point, we want to return back
                $endPointIsFine = in_array($this->fixedStart, $edgeLast->getPoints()) && !in_array($this->fixedStart, $edgeBeforeLast->getPoints());
            }
        }
        if ($startPointIsFine && $endPointIsFine && ($weight < $this->minWeight || $this->minWeight == 0)) {
            $this->minWeight = $weight;
            $this->bestTour = $tour;
        }
    }
}
