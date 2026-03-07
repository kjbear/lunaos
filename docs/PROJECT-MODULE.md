# Requirements for Project module of LunaOS

Let's get the architect involved in this so we can polish this up and have a good architecture for project management.

This needs to be a robust module for the management of projects.  It should be designed to be modular, allowing for the addition of new sub-modules as needed (for instance, adding a tab with a table to reference records in a Tasks table)

Provide the architect with the current Projects module code and schema, and ask for the following, along with suggestions for making this fully functional.

## Data points to be included

- Project name
- Project description
- Project manager (AI agent)
- Link to board discussion that spawned this project (if present)
- Project Status and Phase
- Percent complete
- Github repo information, stats, and link
- Architecture type and listing of technologies (tab - table link)
- Requirements / User Stories - (tab - table link)
- Issues / Problems - (tab - table link)
- Tasks - Linked to user stories of on their own (tab - table link)
- Assigned roles (tab -table link to AI agent record)

## Called out functionality
- I would like to have the creation page have a way to automatically create the repo for the project in github.

## How I think it should work

My thoughts are that a project record should be created to track a project.  Then a project manager and architect would work on creating the user stories and specifications for the code that fulfills those user stories.  Tasks are then created by project manager and linked to the user story.  Along with these tasks, a AI agent specializing in creating tests would write unit tests that are associated with the tasks and/or user story.  A particular AI agent is identified as the proper developer (Dave for PHP code, for instance) and the task is assigned to that agent.  Dave would have a regular polling period for checking for new tasks, and would receive the task and then work on the code.  Dave would start by interfacing with the local git repository and creating a branch for him to work on. Once he is done with the code, he would check in his code.  When his code is checked in, tests would be triggered, including unit, functional, and browser.  If the tests fail, the failure is logged as an issue record, and assigned to Dave to fix.  Once all tests pass, Dave will create a merge request.  The architect and a code quality agent would review the PR to ensure there are no colllisions with other work or branches, then approve the merge.

I welcome thoughts on revising this workflow.


