Feature: View proverbs
  In order to view proverbs
  As a visitor
  I need to be able to visit the proverb's url

  Rules:
  - The title is All is good that ends well
  - The explanation is An event with a good outcome is good, irrespective of the wrongs along the way.

  Scenario: Viewing a proverb
    Given I am on "/proverbs/1"
    Then I should see "All is well that ends well"