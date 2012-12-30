USE [ArenaDB]
GO

/****** Object:  UserDefinedFunction [dbo].[wvc_funct_titleCase]    Script Date: 12/30/2012 11:40:13 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO


-- =============================================
-- Author:		Matt Baylor
-- Create date: 1/18/2011
-- Description:	Title Case input
-- =============================================
CREATE FUNCTION [dbo].[wvc_funct_titleCase] 
(
	-- Add the parameters for the function here
	@In nvarchar(100)
)
RETURNS nvarchar(100)
AS
BEGIN
	-- Declare the return variable here
	DECLARE @Out nvarchar(100)
	DECLARE @Counter int
	
	Set @Counter = 1

	SET @Out = UPPER(SUBSTRING(@In,1,1)) + SUBSTRING(@In,2,LEN(@In)-1)
	
	WHILE @Counter < LEN(@Out)
		BEGIN
			IF SUBSTRING(@Out,@Counter,1) = ' '
				BEGIN
					SET @Out = SUBSTRING(@Out,1,@Counter) +  UPPER(SUBSTRING(@Out,@Counter+1,1)) + SUBSTRING(@Out,@Counter+2,LEN(@Out)-(@Counter+1))
				END
			SET @Counter = @Counter + 1
		END
	
	SET @Counter = 0
	-- Return the result of the function
	RETURN @Out

END


GO

