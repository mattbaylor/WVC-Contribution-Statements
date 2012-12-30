USE [ArenaDB]
GO

/****** Object:  UserDefinedFunction [dbo].[wvc_funct_fundIdfromContributionId]    Script Date: 12/30/2012 11:39:06 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

-- =============================================
-- Author:		Matt Baylor
-- Create date: 1/14/2011
-- Description:	Given a contribution id return the fund id
-- =============================================
CREATE FUNCTION [dbo].[wvc_funct_fundIdfromContributionId] 
(
	-- Add the parameters for the function here
	@ContributionID int
)
RETURNS int
AS
BEGIN
	-- Declare the return variable here
	DECLARE @ReturnVar int

	-- Add the T-SQL statements to compute the return value here
	SELECT @ReturnVar = (
		SELECT ccf.fund_id
		FROM ctrb_contribution_fund ccf
		WHERE ccf.contribution_id = @ContributionID
		)

	-- Return the result of the function
	RETURN (@ReturnVar)

END

GO

