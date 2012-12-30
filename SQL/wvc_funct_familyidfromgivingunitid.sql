USE [ArenaDB]
GO

/****** Object:  UserDefinedFunction [dbo].[wvc_funct_familyIdFromGivingUnitId]    Script Date: 12/30/2012 11:37:36 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		Matt Baylor
-- Create date: 1/14/2011
-- Description:	Given a giving_unit_id determine family_id
-- =============================================
CREATE FUNCTION [dbo].[wvc_funct_familyIdFromGivingUnitId] 
(
	-- Add the parameters for the function here
	@GivingUnitID nvarchar(255)
)
RETURNS int
AS
BEGIN
	-- Declare the return variable here
	DECLARE @ResultVar int

	-- Add the T-SQL statements to compute the return value here
	SELECT @ResultVar = (
		SELECT TOP 1 cfm.family_id
		FROM core_person cp 
			INNER JOIN core_family_member cfm ON cp.person_id = cfm.person_id
		WHERE cp.giving_unit_id = @GivingUnitID
	)

	-- Return the result of the function
	RETURN (@ResultVar)

END

GO

