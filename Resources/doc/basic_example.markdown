# Basic example

This example explain how to have a simple Timeline with `GLOBAL` context.

##Context:

`Chuck norris` just control `the world`. All world resident have to be informed about that !!!!!!!!!


## First step

Add this to your controller:

```php
<?php
use Acme\YourBundle\Entity\TimelineAction
// or other DbDriver TimelineAction.

//.....

public functio myAction()
{
    //......
    $actionManager = $this->get('spy_timeline.action_manager');
    $subject       = $actionManager->findOrCreateComponent('\User', 'chucknorris');
    $action        = $actionManager->create($subject, 'control', array('directComplement' => 'the world));
    $actionManager->updateAction($action);
}
```

But at this moment there is no spread for this action, the timeline action will be stored on your `driver` but nobody will be informed about this.

## Second step

Define your Spread.

Define the service `Acme\MyBundle\Resource\config\services.xml`:
Look at [documentation](http://symfony.com/doc/current/book/service_container.html) to know how to do.

```xml
<service id="my_spread" class="Acme\MyBundle\Spread\MySpread">
    <tag name="spy_timeline.spread"/>
</service>
```

Now, create the class `Acme\MyBundle\Spread\MySpread`

```php
<?php

namespace Acme\MyBundle\Spread;

use Spy\TimelineBundle\Spread\SpreadInterface;
use Spy\TimelineBundle\Model\ActionInterface;
use Spy\TimelineBundle\Spread\Entry\EntryCollection;
use Spy\TimelineBundle\Spread\Entry\EntryUnaware;

class MySpread implements SpreadInterface
{
    public function supports(ActionInterface $action)
    {
        // here you define what actions you want to support, you have to return a boolean.
        if ($action->getSubject()->getName() == "ChuckNorris") {
            return true;
        } else {
            return false;
        }
    }

    public function process(ActionInterface $action, EntryCollection $coll)
    {
        // adding steven seagal to be informed

        $coll->add(new EntryUnaware('\User', 'steven seagal'));

        // get all other users
        $users = MyBestClass::MyBestMethodToGetNerds();

        foreach ($users as $user) {
            $coll->add(new EntryUnaware(get_class($user), $user->getId()));
        }
    }
}
```

## Third step

It's ok, now you can get timeline actions for each users

In your controller:

```php
<?php
public function myAction()
{
    $actionManager   = $this->get('spy_timeline.action_manager');
    $timelineManager = $this->get('spy_timeline.timeline_manager');
    $subject         = $actionManager->findOrCreateComponent('\User', 'steven seagal');

    $timeline = $timelineManager->getTimeline($subject);

    $countEntries = $timelineManager->countEntries($subject);

    return array('coll' => $timeline);
}
```

In your template .twig:

```twig
{% for action in coll %}
    {{ timeline_render(action) }}
    {# i18n ? #}
    {{ i18n_timeline_render(timeline, 'en') }}

{% endfor %}
```

Look at [renderer](https://github.com/stephpy/TimelineBundle/blob/master/Resources/doc/renderer.markdown) to see how to define a path to store verbs.

If you have any question, feel free to create an issue or contact us.
