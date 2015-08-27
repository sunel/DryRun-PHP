# dryrun in PHP (JUST FOR FUN)

This is complete PHP Port of orginal ruby version [dryrun](https://github.com/cesarferreira/dryrun) by [cesarferreira](https://github.com/cesarferreira)

>Did this as a fun project :stuck_out_tongue_winking_eye: :smirk: 

**Try** any **android library** on your **smartphone** **directly** from the **command line**

> A dry run is a testing process where the effects of a possible failure are intentionally mitigated. For example, an aerospace company may conduct a "dry run" test of a jet's new pilot ejection seat while the jet is parked on the ground, rather than while it is in flight.

<p align="center">
<img src="https://raw.githubusercontent.com/cesarferreira/dryrun/master/extras/usage.gif" width="100%" />
</p>


## Usage
> dryrun https://github.com/cesarferreira/android-helloworld


Wait a few seconds... and `voilà`! The app is opened on your phone :smiley:

### Goodies

- Private repos can be tested too :smiley:

  - assuming that you have the corresponding `private ssh keys` in your `~./ssh/`

  - > $ dryrun git@github.com:cesarferreira/android-helloworld.git

- No need to cleanup after you test the library.
  - Your operating system will clean the /tmp/ folder for you.

- No need to wait for **Android Studio** to load.

## Alternative scenario (if you don't use `dryrun`)

1. Find the github's repository url
2. Click the `download zip`
3. Extract the `zip file`
4. Open Android Studio
5. Import the project you just downloaded
6. Sync gradle
7. Run the project
8. Choose the device you want to run
9. Test all you want
10. Delete the `project folder` and the `zip file` when you don't want it anymore

## Installation

    $ gem install dryrun


**Requirements `(if you haven't already)`:**

> $ANDROID_HOME defined on the environment variables [(how-to)](http://stackoverflow.com/questions/5526470/trying-to-add-adb-to-path-variable-osx)


> Android SDK in your $PATH [(how-to)](http://stackoverflow.com/questions/19986214/setting-android-home-enviromental-variable-on-mac-os-x)

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/sunel/DryRun-PHP.