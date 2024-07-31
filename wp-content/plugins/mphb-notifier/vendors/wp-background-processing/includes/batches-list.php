<?php

namespace MPHB\Notifier\Async;

/**
 * @since 1.0
 */
class BatchesList implements \Iterator
{
    protected $process = 'wpbg_process'; // Name of the backgorund process

    /**
     * @var array [Batch name => TasksList object or NULL]. If NULL then load
     * the batch only when required (lazy loading).
     */
    protected $batches = [];
    protected $batchNames = [];
    protected $currentIndex = 0;
    protected $lastIndex = -1;

    /**
     * @param string $process Process name.
     * @param array $batches [Batch name => TasksList object or NULL]
     */
    public function __construct($process, $batches)
    {
        $this->process    = $process;
        $this->batches    = $batches;
        $this->batchNames = array_keys($batches);
        $this->lastIndex  = count($this->batchNames) - 1;
    }

    public function removeBatch($batchName)
    {
        if (array_key_exists($batchName, $this->batches)) {
            $this->batches[$batchName]->delete();
            unset($this->batches[$batchName]);

            // It's not necessary to remove $batchName from $this->batchNames
            // here. After rewind() all will be OK
        }
    }

    public function save()
    {
        foreach ($this->batches as $batch) {
            if (!is_null($batch)) {
                $batch->save();
            }
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->batches);
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->count() == 0;
    }

    /**
     * Iterator method.
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        // Reset the list of names (get rid of removed ones)
        $this->batchNames = array_keys($this->batches);
        $this->currentIndex = 0;
        $this->lastIndex = count($this->batchNames) - 1;
    }

    /**
     * Iterator method.
     *
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->currentIndex <= $this->lastIndex;
    }

    /**
     * Iterator method.
     *
     * @return \MPHB\Notifier\Async\TasksList
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        $currentBatchName = $this->key();
        $currentBatch = $this->batches[$currentBatchName];

        // Load the batch
        if (is_null($currentBatch)) {
            $batchTasks = get_option($currentBatchName, []);
            $currentBatch = new TasksList($this->process, $batchTasks, $currentBatchName);

            $this->batches[$currentBatchName] = $currentBatch;
        }

        return $currentBatch;
    }

    /**
     * Iterator method.
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->batchNames[$this->currentIndex];
    }

    /**
     * Iterator method.
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        // We will not have "gaps" in indexes because we always remove only the
        // current batch
        $this->currentIndex++;
    }

    /**
     * Get value of read-only field.
     *
     * @param string $name Field name.
     * @return mixed Field value or NULL.
     */
    public function __get($name)
    {
        if (in_array($name, ['process'])) {
            return $this->$name;
        } else {
            return null;
        }
    }

    /**
     * @param string $process Process name.
     * @param array $tasks Optional.
     * @param int $batchSize Optional. 100 by default. Use only in conjunction
     *     with $tasks.
     * @return static
     *
     * @global \wpdb $wpdb
     */
    public static function create($process, $tasks = null, $batchSize = 100)
    {
        global $wpdb;

        if (is_null($tasks)) {
            // Get the batches from the database
            $query = "SELECT `option_name` FROM {$wpdb->options} WHERE `option_name` LIKE %s ORDER BY `option_id` ASC";
            $names = $wpdb->get_col($wpdb->prepare($query, "{$process}\_batch\_%")); // Escape wildcard "_"

            // [Batch name => null]
            $batches = array_combine($names, array_fill(0, count($names), null));
        } else {
            // Create batches on existing tasks
            $chunks = array_chunk($tasks, $batchSize);
            $batches = [];

            foreach ($chunks as $tasksChunk) {
                $batch = new TasksList($process, $tasksChunk);
                $batches[$batch->name] = $batch;
            }
        }

        return new static($process, $batches);
    }

    /**
     * @param string $process Process name.
     * @return bool
     *
     * @global \wpdb $wpdb
     */
    public static function hasMore($process)
    {
        global $wpdb;

        $query = "SELECT COUNT(*) FROM {$wpdb->options} WHERE `option_name` LIKE %s";
        $count = (int)$wpdb->get_var($wpdb->prepare($query, "{$process}\_batch\_%")); // Escape wildcard "_"

        return $count > 0;
    }

    /**
     * @param string $process Process name.
     *
     * @global \wpdb $wpdb
     */
    public static function removeAll($process)
    {
        global $wpdb;

        $query = "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE %s";
        $wpdb->query($wpdb->prepare($query, "{$process}\_batch\_%")); // Escape wildcard "_"
    }
}
