# Matomo PHP Tracker Changelog

This is the Developer Changelog for Matomo PHP Tracker. All breaking changes or new features are listed below.


## Matomo PHP Tracker 4.0.0

- Support for new page performance metrics (added in Matomo 4) has been added. You can use `setPerformanceTimings()` to set them for page views.
- Setting page generation time using `setGenerationTime()` has been discontinued. The method still exists to not break applications still using it, but it does not have any effect. Please use new page performance metrics as replacement.
- Sending requests using cURL will now throw an exception if an error occurs in a request
- Matomo does not longer support tracking of these browser plugins: Gears